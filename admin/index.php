<?php
//##copyright##

$iaMailerPlugin = $iaCore->factoryPlugin('mailer', iaCore::ADMIN, 'mailerplugin');

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	if (!empty($_GET['action']))
	{
		$messages = array();
		$id = empty($_GET['id']) ? -1 : (int)$_GET['id'];

		switch ($_GET['action'])
		{
			case 'toggleQueue':
				$iaMailerPlugin->toggleQueue($id);

				$messages[] = iaLanguage::get('saved');

				break;

			case 'remove':
				$iaMailerPlugin->removeQueue($id);

				$messages[] = iaLanguage::get('queue_removed');
		}

		$iaView->setMessages($messages, iaView::SUCCESS);
	}
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	if (iaCore::ACTION_ADD == $pageAction)
	{
		$html = 0;

		if (isset($_POST['save']))
		{
			if($_POST['type'] == 'text')
			{
				$body = str_replace("'", "\'", $_POST['body']);
			}
			else
			{
				$body = str_replace("'", "\'", $_POST['html_body']);
				$html = 1;
			}

			list($error, $msg) = $iaMailerPlugin->createQueue($_POST['from_name'], $_POST['from_mail'], $_POST['subj'], $body, $html, $_POST['groups'], $_POST['st']);
			$iaView->setMessages($msg, ($error ? iaView::ERROR : iaView::SUCCESS));

			if (!$error)
			{
				$iaUtil = iaCore::util();
				iaUtil::go_to(IA_ADMIN_URL . 'mailer/');
			}
		}

		if (empty($_POST))
		{
			$data = array(
				'from_name' => iaUsers::getIdentity()->fullname,
				'from_mail' => iaUsers::getIdentity()->email,
				'type' => 'html',
				'subj' => '',
				'body' => ''
			);
		}
		else
		{
			$data = $_POST;
		}

		$statuses = $iaMailerPlugin->getAccountsStatusList();

		$iaView->assign('data', $data);
		$iaView->assign('statuses', $statuses);
		$iaView->assign('usergroups', $iaCore->factory('users')->getUsergroups());
		$iaView->assign('check', (!empty($_POST['type']) && 'html' == $_POST['type'] ? true : false));
	}
	else
	{
		if ($queue = $iaMailerPlugin->getQueues())
		{
			$iaView->assign('queue', $queue);
		}
	}
}