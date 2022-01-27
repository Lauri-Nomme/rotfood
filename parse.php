<?php

function compose($email, $target) {
	list($subject, $body) = getPlain($email);
	$body = substr($body, 0, strpos($body, "\nAsukoht kaardil\n"));

	$venues = explode("**", $body);
	$venues = array_slice($venues, 2);
	$venues = array_map('parseVenue', $venues);

	$blocks = array_map('block', $venues);
	$blocks = array_merge([[(object)['type' => 'header', 'text' => (object)['type' => 'plain_text', 'text' => $subject]]]], $blocks);
	return (object)['channel' => $target, 'blocks' => array_merge(...$blocks)];
}

function block($venue) {
	$tk = $venue[2] ? "\n---\n{$venue[2]}" :"";
	$url = 'http://' . $venue[1];
	$res = [(object)[
		'type' => 'section',
		'text' => (object)[
			'type' => 'mrkdwn',
			'text' => "<{$url}|*{$venue[0]}*>\n" . $venue[3] . $tk
		]
	], (object)['type' => 'divider']];
	return $res;
}

function parseVenue($venue) {
	list($name, $desc) = explode("------------------------------------------------------------", $venue);
	$name = trim($name);
	$desc = trim($desc);
	$desc = trim(substr($desc, 0, strrpos($desc, "\n")));
	list($url, $desc) = explode("\n", $desc, 2);

	$tk = strripos($desc, "telli kaasa");
	if ($tk !== false) {
		$contact = substr($desc, $tk);
		$desc = substr($desc, 0, $tk);
	} else {
		$contact = "";
	}

	return [$name, $url, $contact, trim($desc)];
}

function getPlain($email) {
	$mail = mailparse_msg_create();
	mailparse_msg_parse($mail, $email);
	$struct = mailparse_msg_get_structure($mail);
	$subject = null;

	foreach ($struct as $st) {
		$section = mailparse_msg_get_part($mail, $st);
		$info = mailparse_msg_get_part_data($section);

		if (!$subject && $info['headers']['subject']) {
			$subject = $info['headers']['subject'];
			$subject = iconv_mime_decode($subject);
		}	  

		if ($info['content-type'] == 'text/plain') { 
			return [$subject, mailparse_msg_extract_part($section, $email, null)];
		}
	}

	return null;
}


?>
