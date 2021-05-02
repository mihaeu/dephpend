<?php

const DEPHPEND_BIN = PHP_BINARY
    . ' -n '
    . ' -d extension=tokenizer.so'
    . ' -d extension=json.so '
    . ' -d extension=mbstring.so '
    . __DIR__ . '/../../bin/dephpend';
const SRC_PATH = __DIR__ . '/../../src';
