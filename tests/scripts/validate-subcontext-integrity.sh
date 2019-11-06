#!/bin/bash
#
# Ensures that the code of the deprecated subcontext of the Webtools Maps module
# is identical to the WebtoolsMapsContext.

# The new, shiny context.
SOURCE_FILE=./tests/Behat/WebtoolsMapsContext.php
# The old, deprecated subcontext.
TARGET_FILE=./modules/oe_webtools_maps/oe_webtools_maps.behat.inc

# Calculate the lines to test. We ignore everything up to the class definition
# since both files have different namespaces and class documentation.
LINE_COUNT=$(($(wc -l $SOURCE_FILE | cut -d ' ' -f1) - $(grep -n '^class WebtoolsMapsContext' $SOURCE_FILE | cut -d : -f1)))

# Check that the code inside the class is identical.
diff <(tail -n $LINE_COUNT $SOURCE_FILE) <(tail -n $LINE_COUNT $TARGET_FILE)

exit $?
