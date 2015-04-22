<?php

require 'include/header.php';

$mode = required_param('mode', PARAM_RAW);
if ($hwblock->setMode($mode)) {
	redirect('/blocks/homework');
} else {
	die('Invalid mode.');
}
