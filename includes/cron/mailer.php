<?php
//##copyright##

if ($queue = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, null, 'mailer_recipients'))
{
	$iaDb->setTable('mailer_messages');

	$iaMailer = $iaCore->factory('mailer');

	$recipients = explode(',', $queue['recipients']);
	$stmt = '`id` = :id AND `active` = :status';
	$iaDb->bind($stmt, array('id' => $queue['message_id'], 'status' => 1));
	if ($m = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, $stmt))
	{
		if ($m['html'])
		{
			$iaMailer->isHTML(true);
		}

		$iaMailer->clearAddresses();
		$iaMailer->FromName = $m['from_name'];
		$iaMailer->From = $m['from_mail'];
		$iaMailer->Subject = $m['subj'];
		$iaMailer->Body = $m['body'];

		foreach($recipients as $email)
		{
			$iaMailer->addAddress($email);
		}

		$iaMailer->send();

		$iaDb->delete(iaDb::convertIds($queue['id']), 'mailer_recipients');

		$iaDb->exists('`message_id` =  ' . $m['id'], null, 'mailer_recipients')
			? $iaDb->update(null, '`id` = ' . $m['id'], array('total' => '`total` - ' . count($recipients)))
			: $iaDb->delete(iaDb::convertIds($m['id']));
	}

	$iaDb->resetTable();
}