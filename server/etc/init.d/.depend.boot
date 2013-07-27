TARGETS = hostname.sh udev networking ifplugd ntp cron ssh lighttpd p1read rc.local
INTERACTIVE =
networking: udev
ifplugd: udev
lighttpd: networking ifplugd
ssh: networking ifplugd
ntp: networking ifplugd
cron: networking ifplugd ntp
p1read: networking ifplugd
rc.local: networking ifplugd ntp
