<?php

@header('Location: ../../?duo-callback&' . http_build_query($_GET));
