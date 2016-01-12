<?php
//##copyright##

class iaMailerPlugin extends abstractPlugin
{
	const PLUGIN_NAME = 'mailer';

	protected static $_tableMessages = 'mailer_messages';
	protected static $_tableRecipients = 'mailer_recipients';


	// table name getters
	public static function getTableMessages()
	{
		return self::$_tableMessages;
	}

	public static function getTableRecipients()
	{
		return self::$_tableRecipients;
	}

	public function createQueue($fromName, $fromMail, $subject, $body, $html, $groups, $status)
	{
		$error = false;
		$rcptsPerRun = 15;
		$messages = array();
		$data = array();

		$data['from_name'] = $fromName;
		$data['subj'] = $subject;
		$data['html'] = $html;

		if (empty($fromMail) || !iaValidate::isEmail($fromMail))
		{
			$error = true;
			$messages[] = iaLanguage::get('from_email_err');
		}
		else
		{
			$data['from_mail'] = $fromMail;
		}

		if (empty($body))
		{
			$error = true;
			$messages[] = iaLanguage::get('err_message');
		}
		else
		{
			$data['body'] = $body;
		}

		$usergrp = empty($groups) ? 0 : array_sum($groups);
		$status = empty($status) ? array() : $status;
		$status = implode("','", $status);

		$rcpt = $this->iaDb->onefield('email', "`usergroup_id` & $usergrp AND `status` IN ('$status')", 0, 0, 'members');

		if (empty($rcpt))
		{
			$error = true;
			$messages[] = iaLanguage::get('no_rcpt');
		}

		if (!$error)
		{
			$data['total'] = count($rcpt);

			$messageId = $this->iaDb->insert($data, null, self::getTableMessages());

			foreach ($rcpt as $index => $addr)
			{
				$rcptCart[] = $addr;

				if (($index + 1) % $rcptsPerRun == 0 || $index + 1 == $data['total'])
				{
					$this->iaDb->insert(array('message_id' => $messageId, 'recipients' => implode(',', $rcptCart)), null, self::getTableRecipients());
					$rcptCart = array();
				}
			}

			$messages[] = iaLanguage::get('queue_added');
		}

		return array($error, $messages);
	}

	public function getQueues()
	{
		return $this->iaDb->all(array('id', 'subj', 'active', 'total'), iaDb::EMPTY_CONDITION, null, null, self::getTableMessages());
	}

	public function toggleQueue($id)
	{
		$this->iaDb->update(null, iaDb::convertIds($id), array('active' => 'IF (1 <> `active`, 1, 0)'), self::getTableMessages());
	}

	public function removeQueue($id)
	{
		$this->iaDb->delete("`id`='$id'", self::getTableMessages());
		$this->iaDb->delete("`message_id`='$id'", self::getTableRecipients());
	}

	public function getAccountsStatusList()
	{
		$enum = $this->iaDb->getRow("SHOW COLUMNS FROM `{$this->iaDb->prefix}members` LIKE 'status'");
		$enum = str_replace(array('enum(', ')', "'"), '', $enum['Type']);

		return explode(',', $enum);
	}
}