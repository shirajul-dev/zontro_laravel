import re
import os

source_path = '/Volumes/Project/Personal Project/ZontroPay/PipraPay-Laravel (Non SaaS)/Template Original/tailadmin/index.html'
dest_path = '/Volumes/Project/Personal Project/ZontroPay/PipraPay-Laravel (Non SaaS)/laravel-app/resources/views/merchant/default/pages/dashboard/index.blade.php'

with open(source_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace asset paths
content = content.replace('href="style.css"', 'href="{{ m_asset(\'assets/css/style.css\') }}"')
content = content.replace('src="bundle.js"', 'src="{{ m_asset(\'assets/js/bundle.js\') }}"')
content = content.replace('href="favicon.ico"', 'href="{{ m_asset(\'assets/images/favicon.ico\') }}"')

# Replace src="src/images/..." with src="{{ m_asset('assets/images/...') }}"
content = re.sub(r'src="src/images/([^"]+)"', r'src="{{ m_asset(\'assets/images/\1\') }}"', content)

with open(dest_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Successfully copied and updated asset paths.")
