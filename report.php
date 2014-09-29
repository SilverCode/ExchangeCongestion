<?php

function processLine($line)
{
	$array = preg_split("/[\s]+/", $line);
	return $array[5];
}

function processBlock($block)
{
	return processLine($block[1]);
}

function processData($data)
{
	$result = array();

	foreach ($data as $date=>$day)
	{
		$ping = processBlock($day);
		$result[$date] = $ping;
	}

	return $result;
}

function dumpData($data)
{
	foreach ($data as $date=>$ping)
	{
		echo "\"".date("Y-m-d H:i:s", $date)."\",".round($ping)."\n";
	}
}

$filename = $argv[1];

$fp = fopen($filename, "r");

$data = array();
$key = "";

date_default_timezone_set("Africa/Johannesburg");

while ($line = fgets($fp))
{
	if (strpos($line, "Start:") === 0)
	{
		$date = str_replace("Start: ", "", trim($line));
		$date = strtotime($date);
		$key = $date;
	}
	else if (strpos($line, "HOST:") === FALSE)
	{
		$data[$key][] = trim($line);
	}
}

$result = processData($data);

ksort($result);

dumpData($result);
