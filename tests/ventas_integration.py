"""Tests on disposable MySQL only; never load the repository db.php."""
import concurrent.futures
import http.cookiejar
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import subprocess
import tempfile
import time
import urllib.error
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
assert os.environ.get("VENTAS_TEST_DB") == "ventas_test", "Use only disposable ventas_test"
DB = """<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli('127.0.0.1', 'root', 'test-only', 'ventas_test', 3306);
$conn->set_charset('utf8mb4');
"""
with tempfile.TemporaryDirectory() as directory:
    target = Path(directory)
    for name in ["subir_venta.php", "api_vender.php", "ventas_validacion.php", "ventas_schema.sql", "api_ventas.php", "ventas_almacenamiento.php", "diagnostico_ventas.php"]:
        shutil.copy(ROOT / name, target / name)
    (target / "db.php").write_text(DB)
    def sql(statement):
        code = "require " + json.dumps(str(target / "db.php")) + ";"
        code += "$r=$conn->query(base64_decode($argv[1]));echo json_encode(is_object($r)?$r->fetch_all(MYSQLI_ASSOC):$r);"
        import base64
        return json.loads(subprocess.check_output(["php", "-r", code, base64.b64encode(statement.encode()).decode()]))
    sql("DROP TABLE IF EXISTS ventas_equipos")
    sql("DROP TABLE IF EXISTS equipos")
    sql("""CREATE TABLE equipos (
        id INT PRIMARY KEY, marca VARCHAR(100), nombre VARCHAR(100), imei VARCHAR(30),
        rom VARCHAR(20), ram VARCHAR(20), stock_cab1 INT, stock_cab2 INT, stock_modelo INT
    ) ENGINE=MyISAM""")
    sql("INSERT INTO equipos VALUES (1,'SAMSUNG','A16','S/N','128GB','4GB',12,2,0)")
    repair_code = "require " + json.dumps(str(target / "db.php")) + "; require " + json.dumps(str(target / "ventas_almacenamiento.php")) + "; echo json_encode(ventas_reparar_almacenamiento($conn));"
    repaired = json.loads(subprocess.check_output(["php", "-r", repair_code]))
    assert repaired[0]["filas"] == 1
    backup = repaired[0]["respaldo"]
    assert sql("SELECT * FROM equipos") == sql("SELECT * FROM " + backup)
    assert sql("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='equipos'")[0]["ENGINE"] == "InnoDB"
    assert json.loads(subprocess.check_output(["php", "-r", repair_code])) == []
    server = subprocess.Popen(["php", "-S", "127.0.0.1:8765", "-t", directory],
        env={**os.environ, "PHP_CLI_SERVER_WORKERS": "4"},
        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, start_new_session=True)
    try:
        jar = http.cookiejar.CookieJar()
        client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
        for _ in range(40):
            try:
                page = client.open("http://127.0.0.1:8765/subir_venta.php").read().decode()
                break
            except urllib.error.URLError:
                time.sleep(.1)
        else:
            raise AssertionError("PHP server did not start")
        try:
            client.open("http://127.0.0.1:8765/api_ventas.php")
            raise AssertionError("Sales export must require login")
        except urllib.error.HTTPError as error:
            assert error.code == 403
        csrf = re.search(r'name="csrf" value="([^"]+)"', page)[1]
        base = dict(csrf=csrf, solicitud=secrets.token_hex(32), id="1",
            columna="stock_cab1", dni="00123456", telefono="987654321", imei="123456789012345",
            vendedor="Zinedin", tipo="contado", precio_contado="500.50", descuento_contado="20.20")
        cookie = "; ".join(c.name + "=" + c.value for c in jar)
        def post(data):
            request = urllib.request.Request("http://127.0.0.1:8765/api_vender.php",
                data=json.dumps(data).encode(),
                headers={"Content-Type": "application/json", "Cookie": cookie})
            try:
                response = urllib.request.urlopen(request)
            except urllib.error.HTTPError as error:
                response = error
            return response.status, json.loads(response.read())
        def fresh(**changes):
            return {**base, "solicitud": secrets.token_hex(32), **changes}
        def count():
            return int(sql("SELECT COUNT(*) AS n FROM ventas_equipos")[0]["n"])
        status, result = post(base)
        assert status == 201 and result["total_centimos"] == 48030, (status, result)
        assert count() == 1
        assert sql("SELECT vendedor FROM ventas_equipos")[0]["vendedor"] == "Zinedin"
        assert sql("SELECT imei FROM ventas_equipos")[0]["imei"] == "123456789012345"
        assert int(sql("SELECT stock_cab1 FROM equipos")[0]["stock_cab1"]) == 11
        assert post(base)[1]["id"] == result["id"] and count() == 1
        assert post({**base, "precio_contado": "600"})[0] == 409
        credit = fresh(tipo="credito", financiera="PayJoy", precio_credito="900",
                       inicial="100.10", descuento_inicial="10.05", columna="stock_cab2")
        assert post(credit)[1]["total_centimos"] == 9005
        assert post(fresh(descuento_contado=""))[1]["total_centimos"] == 50050
        for changes in [
            {"vendedor": ""}, {"vendedor": "Otro"}, {"vendedor": ["Jordy"]}, {"dni": "123"}, {"imei": "123"}, {"telefono": "123"}, {"precio_contado": "-1"},
            {"precio_contado": "0"}, {"descuento_contado": "501"},
            {"precio_contado": "1.001"}, {"precio_contado": "1e2"},
            {"columna": "stock_cab1 = 0"}, {"id": []}, {"tipo": "otro"},
            {"tipo": "credito", "financiera": "", "precio_credito":"500", "inicial":"50"},
            {"tipo": "credito", "financiera": "X", "precio_credito":"50", "inicial":"51"},
            {"tipo": "credito", "financiera": "X", "precio_credito":"50", "inicial":"10", "descuento_inicial":"11"},
        ]:
            assert post(fresh(**changes))[0] == 422, changes
        assert post(fresh(csrf="invalid"))[0] == 403
        assert post(fresh(columna="stock_modelo"))[0] == 409
        assert post(fresh(id="999"))[0] == 409
        assert count() == 3
        sql("CREATE TRIGGER reject_update BEFORE UPDATE ON equipos FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test rollback'")
        assert post(fresh())[0] == 500
        assert count() == 3
        sql("DROP TRIGGER reject_update")
        sql("UPDATE equipos SET stock_modelo = 1")
        with concurrent.futures.ThreadPoolExecutor(2) as pool:
            results = list(pool.map(post, [fresh(columna="stock_modelo"), fresh(columna="stock_modelo")]))
        assert sorted(s for s, _ in results) == [201, 409], results
        assert int(sql("SELECT stock_modelo FROM equipos")[0]["stock_modelo"]) == 0
        stock_before = int(sql("SELECT stock_cab1 FROM equipos")[0]["stock_cab1"])
        same = fresh()
        with concurrent.futures.ThreadPoolExecutor(2) as pool:
            results = list(pool.map(post, [same, same]))
        assert all(s in (200, 201) for s, _ in results), results
        assert len({r["id"] for _, r in results}) == 1
        assert int(sql("SELECT stock_cab1 FROM equipos")[0]["stock_cab1"]) == stock_before - 1
        assert count() == 5
        (target / "__login.php").write_text("<?php session_start(); $_SESSION['acceso_concedido']=true;")
        client.open("http://127.0.0.1:8765/__login.php").read()
        exported = json.loads(client.open("http://127.0.0.1:8765/api_ventas.php").read())
        assert len(exported["ventas"]) == 5
        assert exported["ventas"][0]["imei"] == "123456789012345"
        assert exported["ventas"][0]["dni"] == "00123456"
        assert exported["ventas"][0]["vendedor"] == "Zinedin"
        # Simular una instalación con ventas previas y sin la nueva columna.
        sql("ALTER TABLE ventas_equipos DROP COLUMN vendedor")
        legacy = json.loads(client.open("http://127.0.0.1:8765/api_ventas.php").read())
        assert len(legacy["ventas"]) == 5 and all(v["vendedor"] is None for v in legacy["ventas"])
        assert post(fresh(vendedor="Nicol"))[0] == 201
        assert count() == 6
        current = json.loads(client.open("http://127.0.0.1:8765/api_ventas.php").read())
        assert current["ventas"][0]["vendedor"] == "Nicol"
        print("PASS: cash, credit, optional discounts, validation, CSRF, stock, rollback, retries and concurrency")
    finally:
        import signal
        os.killpg(server.pid, signal.SIGTERM)
        server.wait()
