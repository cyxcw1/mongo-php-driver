--TEST--
Test for PHP-744: Support the oplog_replay query flag
--SKIPIF--
<?php if (!MONGO_STREAMS) { echo "skip This test requires streams support"; } ?>
<?php require_once "tests/utils/generic.inc" ?>
--FILE--
<?php
require_once "tests/utils/server.inc";

function check_flag($server, $query, $cursor_options)
{
	// we only care about the query with "ts" in it
	if (isset($query['ts'])) {
		// test that only the 3rd bit is set
		if ($cursor_options['options'] == 1 << 3) {
			echo "Bit 3 (oplog_reply) is set\n";
		} else {
			echo "Not set :-(\n";
		}
	}
}

$ctx = stream_context_create(
	array(
		"mongodb" => array(
			"log_query" => "check_flag",
		)
	)
);

$dsn = MongoShellServer::getStandaloneInfo();

$m = new MongoClient($dsn, array(), array( 'context' => $ctx ));
$db = dbname();
$c = $m->$db->test;

$cursor = $c->find( array('ts' => time() - 86400 ))->setFlag(3); // oplog_reply
foreach( $cursor as $foo ) {}
?>
--EXPECTF--
Bit 3 (oplog_reply) is set
