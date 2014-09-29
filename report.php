<?php
date_default_timezone_set("Africa/Johannesburg");
global $__PROGRAM__;

function processFile($filename)
{
	$fp = fopen($filename, "r");

	$data = array();
	$key = "";

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

	return $result;
}

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

function avg($data)
{
	$total = 0;

	foreach ($data as $item)
	{
		$total += $item;
	}

	return round($total / count($data));
}

function getHourlyAvg($data)
{
	// Sort the date into hour blocks
	$tmpoutput = array();
	foreach ($data as $date=>$ping)
	{
		$hour = date("H:i", $date);
		$day = date("Y-m-d", $date);
		$tmpoutput[$hour][$day] = round($ping);
	}

	ksort($tmpoutput);
	$output = array();

	foreach ($tmpoutput as $hour=>$pings)
	{
		$avg = avg($pings);
		echo "\"$hour\",$avg\n";
		$output[$hour] = $avg;
	}
}

function printUsage()
{
	global $__PROGRAM__;
	echo <<<HEREDOC
Usage: $__PROGRAM__ [--] [options] <file>
If the program is being invoked from the commandline with PHP
you need to use -- to prevent PHP from trying to parse more
command line options.

-h		Group by hours

HEREDOC;
}

function main($argc, $argv)
{
	global $__PROGRAM__;
	$__PROGRAM__ = $argv[0];

	$showHourly = false;
	$filename = $argv[1];

	if ($argc == 1)
	{
		printUsage();
		exit(1);
	}
	else if ($argc == 3 && $argv[1] == "-h")
	{
		$showHourly = true;
		$filename = $argv[2];
	}

	$data = processFile($filename);

	if ($showHourly)
		getHourlyAvg($data);
	else
		dumpData($data);
}

main(count($argv), $argv);
