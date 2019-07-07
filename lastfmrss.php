<?php

require_once ('simple_html_dom.php');
$user = 'thexiffy';
if (isset($_GET['user'])) {
	$user = urlencode ($_GET['user']);
}

$context = stream_context_create(
    array(
        'http' => array(
            'follow_location' => false,
            'timeout' => 10,
        ),
        'ssl' => array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    )
);


if (isset($_GET['loved'])) {
	$type = 'loved';
	$html = file_get_html("https://www.last.fm/user/{$user}/loved?page=1", false, $context);
} else {
	$type = 'played';
	$html = file_get_html("https://www.last.fm/user/{$user}/library?page=1", false, $context);
}


// Start the output
header("Content-type: text/xml; charset=utf-8");
header("Cache-Control:s-maxage=600");
?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <lastBuildDate><?php echo gmdate("D, d M Y H:i:s O", time()) ?></lastBuildDate>
        <language>en</language>
        <title><?php echo $user ?>'s last <?php echo $type ?> tracks on Last.fm</title>
        <description>
            Last.fm last <?php echo $type ?> tracks from <?php echo $user ?>.
        </description>
        <link>http://www.last.fm/<?php echo $user ?></link>
        <ttl>960</ttl>
        <generator>xiffy.nl</generator>
        <category>Personal</category>
<?php

$i = 0;
foreach($html->find('.chartlist-row') as $row) {
	foreach($row->find('.chartlist-name') as $song) {
		$title = $song->find('a',0)->plaintext;
		$link = $song->find('a',0)->href;
	}
	foreach($row->find('.chartlist-artist') as $artist) {
		$artist = $artist->find('a',0)->plaintext;
	}
	$desc = 'https://www.last.fm'. $link;
	$desc = '<a href="'.$desc.'">'.$artist.'</a>';
	foreach($row->find('.chartlist-timestamp') as $timestamp) {
		$span = str_get_html(trim($timestamp->innertext)); // don't ask
		$span->find('span');
		$arr = (array)$span;
		$node = (array) $arr['nodes'][1];
		$da_time = ($node['attr']['title']);

 		$playdate = gmdate("D, d M Y H:i:s O", strtotime($da_time));
	}

	?>
  <item>
  	<title><?php echo $artist.' - '.$title ?> </title>
  	<pubDate><?php echo $playdate; ?></pubDate>
  	<link>http://www.last.fm<?php echo $link ?></link>
    <guid isPermaLink='false'><?php echo $link ?></guid>
  	<description><![CDATA[<?php echo $desc?>]]></description>
  </item>

	<?php
	//echo "{$i}. {$artist} - {$title} \n {$desc}\n";
	$i++;
}

?>
	</channel>
</rss>
