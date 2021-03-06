<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail\Tests\Service;

use Horde_Imap_Client_Socket;
use OCA\Mail\Account;
use OCA\Mail\IMAP\IMAPClientFactory;
use OCA\Mail\IMAP\Sync\Request;
use OCA\Mail\IMAP\Sync\Synchronizer;
use OCA\Mail\Service\FolderMapper;
use OCA\Mail\Service\FolderNameTranslator;
use OCA\Mail\Service\MailManager;
use OCA\Mail\Tests\TestCase;
use OCP\Files\Folder;
use PHPUnit_Framework_TestCase;

class MailManagerTest extends TestCase {

	/** @var IMAPClientFactory|PHPUnit_Framework_TestCase */
	private $imapClientFactory;

	/** @var FolderMapper|PHPUnit_Framework_TestCase */
	private $folderMapper;

	/** @var FolderNameTranslator|PHPUnit_Framework_TestCase */
	private $translator;

	/** @var Synchronizer|PHPUnit_Framework_TestCase */
	private $sync;

	/** @varr MailManager */
	private $manager;

	protected function setUp() {
		parent::setUp();

		$this->imapClientFactory = $this->createMock(IMAPClientFactory::class);
		$this->folderMapper = $this->createMock(FolderMapper::class);
		$this->translator = $this->createMock(FolderNameTranslator::class);
		$this->sync = $this->createMock(Synchronizer::class);

		$this->manager = new MailManager($this->imapClientFactory, $this->folderMapper, $this->translator, $this->sync);
	}

	public function testGetFolders() {
		$client = $this->createMock(Horde_Imap_Client_Socket::class);
		$account = $this->createMock(Account::class);

		$this->imapClientFactory->expects($this->once())
			->method('getClient')
			->willReturn($client);
		$folders = [
			$this->createMock(Folder::class),
			$this->createMock(Folder::class),
		];
		$this->folderMapper->expects($this->once())
			->method('getFolders')
			->with($this->equalTo($account), $this->equalTo($client))
			->willReturn($folders);
		$this->folderMapper->expects($this->once())
			->method('getFoldersStatus')
			->with($this->equalTo($folders));
		$this->folderMapper->expects($this->once())
			->method('detectFolderSpecialUse')
			->with($this->equalTo($folders));
		$this->folderMapper->expects($this->once())
			->method('sortFolders')
			->with($this->equalTo($folders));
		$this->translator->expects($this->once())
			->method('translateAll')
			->with($this->equalTo($folders));
		$this->folderMapper->expects($this->once())
			->method('buildFolderHierarchy')
			->with($this->equalTo($folders));

		$this->manager->getFolders($account);
	}

	public function testSync() {
		$account = $this->createMock(Account::class);
		$syncRequest = $this->createMock(Request::class);
		$client = $this->createMock(Horde_Imap_Client_Socket::class);
		$this->imapClientFactory->expects($this->once())
			->method('getClient')
			->willReturn($client);
		$this->sync->expects($this->once())
			->method('sync')
			->with($client, $syncRequest);

		$this->manager->syncMessages($account, $syncRequest);
	}

}
