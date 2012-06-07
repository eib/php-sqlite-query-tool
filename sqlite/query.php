<?php
// Copyright (c) 2012, Ethan Blackwelder. License: http://eib.mit-license.org/
require_once('lib.php');
no_cache_headers();

$query = array_get($_POST, 'query', '');
$db_name = 'demo.sdb';
$parent_dir = '../demo';
$separator = ';';

$results = array();
$queries = array();

try {
	$pdo = get_sqlite_connection($db_name, $parent_dir);
	
	if ($pdo && $query) {
		$pieces = explode($separator, $query);
		foreach ($pieces as $sql) {
			process_query($sql, $pdo);
		}
	}
	
} catch (Exception $e) {
	$results[] = "Cannot connect to database named \"$db_name\".\nCause: " . $e->getMessage();
}

function process_query($sql, PDO $pdo) {
	global $queries;
	global $results;
	
	$sql = trim($sql);
	if (!empty($sql)) {
		try {
			$queries[] = preg_replace('/[ ]{2,}/', ' ', preg_replace("/[\n\r]+/", " ", $sql));
			$stmt = $pdo->prepare($sql);
			if ('SELECT' != strtoupper(substr($sql, 0, 6)) && 0 !== preg_match('/(DELETE)|(UPDATE)|(INSERT)|(REPLACE)/i', $sql)) {
				$success = $stmt->execute();
				$num_rows_affected = $stmt->rowCount();
				$result = $success ? "Success ($num_rows_affected rows affected)" : "Failed";
				$results[] = $result;
			} else {
				$stmt->execute();
				$result = $stmt->fetchAll();
				$results[] = empty($result) ? "No rows found." : $result;
			}
		} catch (Exception $e) {
			$results[] = 'Exception: ' . $e->getMessage();
		}
	}	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>PHP/SQLite Query Tool</title>
</head>
<body>

<form method="post" action="<?php print $_SERVER['REQUEST_URI']; ?>">
<div style="float:right;">
Scratch: (copy and paste anything here)<br>
<textarea name="scratch" cols="45" rows="30"><?php 
print htmlentities(array_get($_POST, 'scratch', ''));
?></textarea>
</div>
<p>
Type your query/queries here: (separated by "<?php print htmlentities($separator); ?>")<br>
<textarea name="query" cols="100" rows="15"><?php print htmlentities($query); ?></textarea>
<br>
<input type="submit" name="submit" value="Execute">
</p>
<p>
Results:<br>
<textarea id="results" cols="100" rows="25" readonly="readonly"><?php
ob_start();

foreach ($results as $key => $result) {
	$query = array_get($queries, $key);
	if ($query) {
		print "Query #$key: $query;\n";
	}
	if (is_array($result)) {
		foreach ($result as $index => $row) {
			if (is_array($row)) {
				$tmp = array();
				foreach ($row as $field => $value) {
					$tmp[] = "[$field] => $value";
				}
				print "$index:\t" . join("\n\t", $tmp) . "\n";
			} else {
				print "\t[$index] => $row\n";
			}
		}
	} else {
		print "$result\n";
	}
	print "\n";
}

print htmlentities(ob_get_clean());
?></textarea>
</p>
</form>

<p>Copyright &copy; <?php print date('Y'); ?> Ethan Blackwelder. <a href="http://eib.mit-license.org/">License.</a></p>

</body>
</html>