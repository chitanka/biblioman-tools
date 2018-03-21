<?php
$bibliomanDir = realpath($argv[1]);
$parametersYamlFile = "$bibliomanDir/app/config/parameters.yml";
if (!file_exists($parametersYamlFile)) {
	die("File '{$parametersYamlFile}' does not exist.");
}
preg_match_all('#database_(\w+):(.+)#', file_get_contents($parametersYamlFile), $dbConfigMatches);
if (empty($dbConfigMatches)) {
	die("Invalid yaml config");
}
$dbConfig = array_combine($dbConfigMatches[1], array_map('trim', $dbConfigMatches[2]));
if (in_array($dbConfig['port'], ['null', '~', ''])) {
	$dbConfig['port'] = 3306;
}
$pdo = new PDO("mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']}", $dbConfig['user'], $dbConfig['password']);

$dryRun = true;
$dataDir = "$bibliomanDir/data";

function calcSubPath($id) {
	$subDirCount = 4;
	return implode('/', array_slice(str_split(str_pad($id, $subDirCount, '0', STR_PAD_LEFT)), -$subDirCount));
}

function updateFullContent($pdo) {
	update($pdo, 'fullcontent', 'book', 'full_content', 'id');
}

function updateCovers($pdo) {
	update($pdo, 'covers', 'book_cover', 'name', 'book_id');
}

function updateCoversInBookTable($pdo) {
	update($pdo, 'covers', 'book', 'cover', 'id');
}
function updateBackCoversInBookTable($pdo) {
	update($pdo, 'covers', 'book', 'back_cover', 'id');
}

function updateScans($pdo) {
	update($pdo, 'scans', 'book_scan', 'name', 'book_id');
}

function update($pdo, $dir, $table, $field, $bookIdField) {
	$sql = "select id, $bookIdField, $field from $table where $field is not null";
	$sth = $pdo->prepare($sql);
	$sth->execute();
	$data = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($data as $row) {
		$basename = strpos($row[$field], '-') !== false ? explode('-', $row[$field])[1] : $row[$field];
		$newFile = $row[$bookIdField].'-'.$basename;
		$update = "update $table set $field='{$newFile}' where id={$row['id']}";
		execSql($pdo, $update);
		moveFile($dir, $row[$field], $row[$bookIdField], $newFile);
	}
}

function moveFile($dir, $file, $id, $newFile) {
	global $dataDir;
	$dir = $dataDir.'/'.$dir;
	$oldName = "$dir/$file";
	$newName = "$dir/".calcSubPath($id)."/".$newFile;
	renameFile($oldName, $newName);
}

$sqls = [];
function execSql($pdo, $sql) {
	global $sqls, $dryRun;
	echo $sql, "\n";
	$sqls[] = $sql;
	if ($dryRun === true) {
		return;
	}
	$pdo->exec($sql);
}

$moves = [];
function renameFile($oldName, $newName) {
	global $dryRun, $moves;
	if (!file_exists($oldName)) {
		foreach (['.jpg', '.png'] as $ext) {
			if (strpos($oldName, $ext) !== false) {
				$oldName = str_replace($ext, '.tif', $oldName);
				$newName = str_replace($ext, '.tif', $newName);
			}
		}
	}
	if (!file_exists($oldName)) {
		return;
	}
	$command = "mv $oldName $newName";
	echo $command, "\n";
	$moves[] = $command;
	if ($dryRun === true) {
		return;
	}
	$newDir = dirname($newName);
	if (!file_exists($newDir)) {
		mkdir($newDir, 0775, true);
	}
	rename($oldName, $newName);
}

updateScans($pdo);
updateCovers($pdo);
updateCoversInBookTable($pdo);
updateBackCoversInBookTable($pdo);
updateFullContent($pdo);

file_put_contents(__FILE__.'.log.sql', implode(";\n", $sqls));
file_put_contents(__FILE__.'.log.mv.sh', implode("\n", $moves));
