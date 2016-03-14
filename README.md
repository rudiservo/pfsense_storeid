# PfSense Squid 3 storeid helper
Storeid helper for squid3.4 and pfsense 2.6

This software was design for PfSense Squid 3 package
It can work on other Squid 3 deployments but the execute part of php
may have to be changed from:
>\#!/usr/local/bin/php -q

to

>\#!/bin/php

(just and example this may not work, check with your distro)


##How to install and run it on PfSense

You need to have squid3 package installed and proxy configuration already done
all cache memory and hard disk settings, watermarks, etc, should be at already done
and at fit for your needs and limitations.

copy all the file to /var/squid/storeid
you can use scp

> \# scp -r "path_of_storeid" root@your_pfsense:/var/squid/storeid

Change group and Permissions inside your pfsense box
I know its not quite proper but I did had some issues with it

> \# chgrp -Rf proxy storeid/

> \# chown -Rf squid storeid/

> \# chmod -Rf 775 storeid


## Squid Config
on PfSense you on the Squid Proxy Server -> Local Cache
you have to add these on the custom refresh paterns

```

acl cdnsites dstdom_regex -i "/var/squid/storeid/conf/storeid_sites.txt"
store_id_access allow cdnsites
store_id_access deny all
store_id_program /var/squid/storeid/storeid_helper.php /var/squid/storeid/storeid_rewrite
store_id_children 10 startup=5 idle=1 concurrency=0

refresh_pattern ([^.]+\.)?(cs|content[1-9]|hsar|content-origin|client-download).steampowered.com/.*\.* 43200 100% 43200 reload-into-ims ignore-reload ignore-no-store override-expire override-lastmod
refresh_pattern ([^.]+\.)?.akamai.steamstatic.com/.*\.* 43200 100% 43200 reload-into-ims ignore-reload ignore-no-store override-expire override-lastmod

refresh_pattern -i ([^.]+\.)?.adobe.com/.*\.(zip|exe) 43200 100% 43200 reload-into-ims ignore-reload ignore-no-store override-expire override-lastmod
refresh_pattern -i ([^.]+\.)?.java.com/.*\.(zip|exe) 43200 100% 43200 reload-into-ims ignore-reload ignore-no-store override-expire override-lastmod
refresh_pattern -i ([^.]+\.)?.sun.com/.*\.(zip|exe) 43200 100% 43200 reload-into-ims ignore-reload ignore-no-store override-expire override-lastmod
refresh_pattern -i ([^.]+\.)?.oracle.com/.*\.(zip|exe|tar.gz) 43200 100% 43200 reload-into-ims ignore-reload ignore-no-store override-expire override-lastmod

refresh_pattern -i appldnld\.apple\.com 43200 100% 43200 ignore-reload ignore-no-store override-expire override-lastmod
refresh_pattern -i ([^.]+\.)?apple.com/.*\.(ipa) 43200 100% 43200 ignore-reload ignore-no-store override-expire override-lastmod
 
refresh_pattern -i  ([^.]+\.)?.google.com/.*\.(exe|crx) 10080 80% 43200 override-expire override-lastmod ignore-no-cache ignore-reload reload-into-ims ignore-private
refresh_pattern -i ([^.]+\.)?g.static.com/.*\.(exe|crx) 10080 80% 43200 override-expire override-lastmod ignore-no-cache ignore-reload reload-into-ims ignore-private
 
refresh_pattern -i ([^.]+\.)?.ubuntu.com/.*\.(deb) 10080 80% 43200 override-expire override-lastmod ignore-no-cache ignore-reload reload-into-ims ignore-private

```


Save your config

in case you want to debug go to cong/storeid.conf.php and set $_DEBUG =true
you can also change the log file location.

to test you just need to to
> \# ./storeid_helper.php storeid_rewrite

paste a URL to test and it should return a OK

to exit just type quit.


Have fun getting 100% hit's on your PfSense Box.

##TroubleShooting:

If you get this error:
> FATAL: The store_id helpers are crashing too rapidly, need help!

check for permssions





##References

http://wiki.sebeka.k12.mn.us/web_services:squid_update_cache

http://wiki.squid-cache.org/Features/StoreID/DB

http://wiki.squid-cache.org/Features/StoreID

http://wiki.squid-cache.org/ConfigExamples/DynamicContent/YouTube

http://blog.thelifeofkenneth.com/2014/08/using-squid-storeids-to-optimize-steams.html

https://gist.github.com/PhirePhly/76345fa72ecdb6fb1d37#file-squid-conf
