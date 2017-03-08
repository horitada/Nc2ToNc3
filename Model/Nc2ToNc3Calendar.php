<?php
/**
 * Nc2ToNc3Calendar
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Nc2ToNc3AppModel', 'Nc2ToNc3.Model');

/**
 * Nc2ToNc3Calendar
 *
 * @see Nc2ToNc3BaseBehavior
 * @method void writeMigrationLog($message)
 * @method Model getNc2Model($tableName)
 * @method string getLanguageIdFromNc2()
 * @method string convertDate($date)
 * @method string convertLanguage($langDirName)
 * @method array saveMap($modelName, $idMap)
 * @method array getMap($nc2Id)
 * @method void changeNc3CurrentLanguage($langDirName = null)
 * @method void restoreNc3CurrentLanguage()
 *
 * @see Nc2ToNc3CalendarBehavior
 * @method string getLogArgument($nc2Calendar)
 * @method string generateNc3CalendarFrameSettingData($nc2CalendarBlock)
 *
 */
class Nc2ToNc3Calendar extends Nc2ToNc3AppModel {

/**
 * Custom database table name, or null/false if no table association is desired.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#usetable
 */
	public $useTable = false;

/**
 * List of behaviors to load when the model object is initialized. Settings can be
 * passed to behaviors by using the behavior name as index.
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/models/behaviors.html#using-behaviors
 */
	public $actsAs = ['Nc2ToNc3.Nc2ToNc3Calendar'];

/**
 * Migration method.
 *
 * @return bool True on success.
 */
	public function migrate() {
		$this->writeMigrationLog(__d('nc2_to_nc3', 'Calendar Migration start.'));

		/* @var $Nc2CalendarManage AppModel */
		$Nc2CalendarManage = $this->getNc2Model('calendar_manage');
		$nc2CalendarManages = $Nc2CalendarManage->find('all');
		if (!$this->__saveCalendarPermissionFromNc2($nc2CalendarManages)) {
			return false;
		}

		/* @var $Nc2CalendarBlock AppModel */
		$Nc2CalendarBlock = $this->getNc2Model('calendar_block');
		$nc2CalendarBlocks = $Nc2CalendarBlock->find('all');
		if (!$this->__saveCalendarFrameSettingFromNc2($nc2CalendarBlocks)) {
			return false;
		}

		/* @var $Nc2CalendarPlan AppModel */
		$Nc2CalendarPlan = $this->getNc2Model('calendar_plan');
		$nc2CalendarPlans = $Nc2CalendarPlan->find('all');
		if (!$this->__saveCalendarEventFromNc2($nc2CalendarPlans)) {
			return false;
		}

		$this->writeMigrationLog(__d('nc2_to_nc3', 'Calendar Migration end.'));
		return true;
	}

/**
 * Save CalendarPermission from Nc2.
 *
 * @param array $nc2CalendarManages Nc2CalendarManage data.
 * @return bool True on success
 * @throws Exception
 */
	private function __saveCalendarPermissionFromNc2($nc2CalendarManages) {
		$this->writeMigrationLog(__d('nc2_to_nc3', '  CalendarPermission data Migration start.'));

		/* @var $CalendarPermission CalendarPermission */
		$CalendarPermission = ClassRegistry::init('Calendars.CalendarPermission');
		foreach ($nc2CalendarManages as $nc2CalendarManage) {
			$CalendarPermission->begin();
			try {
				$data = $this->generateNc3CalendarPermissionData($nc2CalendarManage);
				if (!$data) {
					$CalendarPermission->rollback();
					continue;
				}

				if (!$CalendarPermission->savePermission($data)) {
					// print_rはPHPMD.DevelopmentCodeFragmentに引っかかった。
					// var_exportは大丈夫らしい。。。
					// @see https://phpmd.org/rules/design.html
					$message = $this->getLogArgument($nc2CalendarManage) . "\n" .
						var_export($CalendarPermission->validationErrors, true);
					$this->writeMigrationLog($message);

					$CalendarPermission->rollback();
					continue;
				}

				$nc2RoomId = $nc2CalendarManage['Nc2CalendarManage']['room_id'];
				$idMap = [
					$nc2RoomId => $CalendarPermission->id
				];
				$this->saveMap('CalendarPermission', $idMap);

				$CalendarPermission->commit();

			} catch (Exception $ex) {
				// NetCommonsAppModel::rollback()でthrowされるので、以降の処理は実行されない
				// $CalendarFrameSetting::savePage()でthrowされるとこの処理に入ってこない
				$CalendarPermission->rollback($ex);
				throw $ex;
			}
		}

		$this->writeMigrationLog(__d('nc2_to_nc3', '  CalendarPermission data Migration end.'));

		return true;
	}

/**
 * Save CalendarFrameSetting from Nc2.
 *
 * @param array $nc2CalendarBlocks Nc2CalendarBlock data.
 * @return bool True on success
 * @throws Exception
 */
	private function __saveCalendarFrameSettingFromNc2($nc2CalendarBlocks) {
		$this->writeMigrationLog(__d('nc2_to_nc3', '  CalendarFrameSetting data Migration start.'));

		/* @var $CalendarFrameSetting CalendarFrameSetting */
		$CalendarFrameSetting = ClassRegistry::init('Calendars.CalendarFrameSetting');
		foreach ($nc2CalendarBlocks as $nc2CalendarBlock) {
			$CalendarFrameSetting->begin();
			try {
				$data = $this->generateNc3CalendarFrameSettingData($nc2CalendarBlock);
				if (!$data) {
					$CalendarFrameSetting->rollback();
					continue;
				}

				if (!$CalendarFrameSetting->saveFrameSetting($data)) {
					// print_rはPHPMD.DevelopmentCodeFragmentに引っかかった。
					// var_exportは大丈夫らしい。。。
					// @see https://phpmd.org/rules/design.html
					$message = $this->getLogArgument($nc2CalendarBlocks) . "\n" .
						var_export($CalendarFrameSetting->validationErrors, true);
					$this->writeMigrationLog($message);

					$CalendarFrameSetting->rollback($ex);
					continue;
				}

				$nc2BlockId = $nc2CalendarBlock['Nc2CalendarBlock']['block_id'];
				$idMap = [
					$nc2BlockId => $CalendarFrameSetting->id
				];
				$this->saveMap('CalendarFrameSetting', $idMap);

				$CalendarFrameSetting->commit();

			} catch (Exception $ex) {
				// NetCommonsAppModel::rollback()でthrowされるので、以降の処理は実行されない
				// $CalendarFrameSetting::savePage()でthrowされるとこの処理に入ってこない
				$CalendarFrameSetting->rollback($ex);
				throw $ex;
			}
		}

		$this->writeMigrationLog(__d('nc2_to_nc3', '  CalendarFrameSetting data Migration end.'));

		return true;
	}

/**
 * Save CalendarEvent from Nc2.
 *
 * @param array $nc2CalendarPlans Nc2CalendarPlan data.
 * @return bool True on success
 * @throws Exception
 */
	private function __saveCalendarEventFromNc2($nc2CalendarPlans) {
		$this->writeMigrationLog(__d('nc2_to_nc3', '  CalendarEvent data Migration start.'));

		/* @var $CalendarActionPlan CalendarActionPlan */
		/* @var $CalendarEvent CalendarEvent */
		$CalendarActionPlan = ClassRegistry::init('Calendars.CalendarActionPlan');
		$CalendarEvent = ClassRegistry::init('Calendars.CalendarEvent');
		foreach ($nc2CalendarPlans as $nc2CalendarPlan) {
			$CalendarActionPlan->begin();
			try {
				$data = $this->generateNc3CalendarActionPlanData($nc2CalendarPlan);
				if (!$data) {
					$CalendarActionPlan->rollback();
					continue;
				}

				if (!$this->__saveCalendarEventFromGeneratedData($nc2CalendarPlan, $data)) {
					$CalendarActionPlan->rollback($ex);
					continue;
				}

				// CalendarActionPlan::saveCalendarPlan から、まわりまわってCalendarEvent::save が呼ばれるので、
				// CalendarEvent::idで取得できる
				$nc2CalendarId = $nc2CalendarPlan['Nc2CalendarPlan']['calendar_id'];
				$idMap = [
					$nc2CalendarId => $CalendarEvent->id
				];
				$this->saveMap('CalendarActionPlan', $idMap);

				$CalendarActionPlan->commit();

			} catch (Exception $ex) {
				// NetCommonsAppModel::rollback()でthrowされるので、以降の処理は実行されない
				// $CalendarActionPlan->saveCalendarPlanでthrowされるとこの処理に入ってこない
				$CalendarActionPlan->rollback($ex);
				throw $ex;
			}
		}

		$this->writeMigrationLog(__d('nc2_to_nc3', '  CalendarEvent data Migration end.'));

		return true;
	}

/**
 * Save CalendarEvent from denerated data.
 *
 * @param array $nc2CalendarPlan Nc2CalendarPlan data.
 * @param array $nc3ActionPlan Nc3CalendarActionPlan data.
 * @return bool True on success
 * @throws Exception
 */
	private function __saveCalendarEventFromGeneratedData($nc2CalendarPlan, $nc3ActionPlan) {
		// CalendarActionPlan::saveCalendarPlan呼び出し前の処理
		// @see https://github.com/NetCommons3/Calendars/blob/3.1.0/Controller/CalendarPlansController.php#L382-L401

		/* @var $CalendarActionPlan CalendarActionPlan */
		/* @var $CalendarEvent CalendarEvent */
		$CalendarActionPlan = ClassRegistry::init('Calendars.CalendarActionPlan');
		$CalendarEvent = ClassRegistry::init('Calendars.CalendarEvent');

		// origin_event_idは更新前のCalendarEvent.id
		// @see https://github.com/NetCommons3/Calendars/blob/3.1.0/View/Elements/CalendarPlans/detail_edit_hiddens.ctp#L16
		$nc3EventId = $nc3ActionPlan['CalendarActionPlan']['origin_event_id'];
		$nc3Event = $CalendarEvent->getEventById($nc3EventId);

		// 更新処理でしか使われてなさげだが、同じような処理にしとく
		// @see https://github.com/NetCommons3/Calendars/blob/3.1.0/Model/CalendarActionPlan.php#L573-L598
		$saveParameters = $CalendarActionPlan->getProcModeOriginRepeatAndModType($nc3ActionPlan, $nc3Event);
		list($addOrEdit, $isRepeatEvent, $isChangedDteTime, $isChangedRepetition) = each($saveParameters);

		// Nc2CalendarPlan.insert_user_idに対応するNc3User.idで良い？
		// @see https://github.com/NetCommons3/Calendars/blob/3.1.0/Model/Behavior/CalendarInsertPlanBehavior.php#L165-L171
		// @see https://github.com/NetCommons3/Calendars/blob/3.1.0/Model/Behavior/CalendarUpdatePlanBehavior.php#L381-L383
		/* @var $Nc2ToNc3User Nc2ToNc3User */
		$Nc2ToNc3User = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3User');
		$nc3CreatedUser = $Nc2ToNc3User->getCreatedUser($nc2CalendarPlan['Nc2CalendarPlan']);

		// プライベートルームIDは共有イベントでしか使ってなさげ。しかもidとして使用されていない感じ。とりあえずnull
		// @see https://github.com/NetCommons3/Calendars/blob/3.1.0/Controller/CalendarPlansController.php#L589-L596
		// @see https://github.com/NetCommons3/Calendars/blob/3.1.0/Model/Behavior/CalendarPlanGenerationBehavior.php#L276-L283
		$nc3PivateRoomId = null;

		$nc3EventId = $CalendarActionPlan->saveCalendarPlan(
			$addOrEdit,
			$isRepeatEvent,
			$isChangedDteTime,
			$isChangedRepetition,
			$nc3CreatedUser,
			$nc3PivateRoomId
		);
		if (!$nc3EventId) {
			// print_rはPHPMD.DevelopmentCodeFragmentに引っかかった。
			// var_exportは大丈夫らしい。。。
			// @see https://phpmd.org/rules/design.html
			$message = $this->getLogArgument($nc2CalendarPlan) . "\n" .
			var_export($CalendarActionPlan->validationErrors, true);
			$this->writeMigrationLog($message);

			return false;
		}

		return true;
	}

}

