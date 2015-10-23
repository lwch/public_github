#!/bin/sh
mysql -uroot public_github <<EOF
drop table if exists repos_log;
EOF
mysql -uroot public_github < repos_log.sql
