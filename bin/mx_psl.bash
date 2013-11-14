#!/bin/bash

PSL_DATA_DIR="$(dirname $0)/../data"
PSL_URL="https://mxr.mozilla.org/mozilla-central/source/netwerk/dns/effective_tld_names.dat?raw=1"
PSL_REGEX="^[^/\!]"
PSL_LOCAL_FILE="mozilla_psl.txt"

wget -qO- "$PSL_URL" | grep "$PSL_REGEX" > "$PSL_DATA_DIR/$PSL_LOCAL_FILE"

[ $? == 0 ] && echo OK || echo KO
exit 0