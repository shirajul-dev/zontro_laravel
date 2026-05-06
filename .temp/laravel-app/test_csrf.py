import urllib.request
import re
import urllib.parse

req1 = urllib.request.Request("http://127.0.0.1:8000/admin/dashboard")
with urllib.request.urlopen(req1) as f:
    html = f.read().decode('utf-8')
    cookie_header = f.info().get('Set-Cookie')

m = re.search(r'laravel-session=([^;]+)', cookie_header)
session = m.group(1)

m2 = re.search(r'name="csrf_token_default" value="([^"]+)"', html)
token = m2.group(1)

print(f"Token: {token}")

data = urllib.parse.urlencode({'action': 'customers-info-byID', 'ItemID': '1', 'csrf_token': token}).encode('utf-8')
req2 = urllib.request.Request("http://127.0.0.1:8000/admin/dashboard", data=data)
req2.add_header('X-Requested-With', 'XMLHttpRequest')
req2.add_header('Cookie', f'laravel-session={session}')

with urllib.request.urlopen(req2) as f2:
    print(f"Ajax Response: {f2.read().decode('utf-8')}")

