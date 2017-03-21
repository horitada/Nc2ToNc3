<?php
/**
 * Nc2ToNc3QuestionnaireBehavior
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Nc2ToNc3QuestionBaseBehavior', 'Nc2ToNc3.Model/Behavior');

/**
 * Nc2ToNc3QuestionnaireBehavior
 *
 */
class Nc2ToNc3QuestionnaireBehavior extends Nc2ToNc3QuestionBaseBehavior {

/**
 * Get Log argument.
 *
 * @param Model $model Model using this behavior.
 * @param array $nc2Questionnaire Array data of Nc2Questionnaire, Nc2CalendarBlock and Nc2CalendarPlan.
 * @return string Log argument
 */
	public function getLogArgument(Model $model, $nc2Questionnaire) {
		return $this->__getLogArgument($nc2Questionnaire);
	}

/**
 * Generate Nc3Questionnaire data.
 *
 * Data sample
 * data[Questionnaire][import_key]:
 * data[Questionnaire][export_key]:
 * data[Questionnaire][title_icon]:
 * data[Questionnaire][title]:
 * data[Questionnaire][sub_title]:
 * data[Questionnaire][answer_timing]:0
 * data[Questionnaire][answer_start_period]:
 * data[Questionnaire][answer_end_period]:
 * data[Questionnaire][total_show_timing]:0
 * data[Questionnaire][total_show_start_period]:
 * data[Questionnaire][is_answer_mail_send]:0
 * data[Questionnaire][is_no_member_allow]:0
 * data[Questionnaire][is_key_pass_use]:0
 * data[AuthorizationKey][authorization_key]:
 * data[Questionnaire][is_image_authentication]:0
 * data[Questionnaire][is_anonymity]:0
 * data[Questionnaire][thanks_content]:
 * data[QuestionnairePage][0][page_sequence]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][key]:
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][question_sequence]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][question_value]:新規質問1
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][question_type]:1
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][description]:
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][is_require]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][question_type_option]:
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][is_choice_random]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][is_choice_horizon]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][is_skip]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][is_range]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][min]:
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][max]:
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][is_result_display]:1
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][result_display_type]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][key]:19d7cb6c3045c3c54415446e2a3c71ae
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][matrix_type]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][other_choice_type]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][choice_sequence]:0
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][choice_label]:新規選択肢1
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][choice_value]:
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][skip_page_sequence]:99999
 * data[QuestionnairePage][0][QuestionnaireQuestion][0][QuestionnaireChoice][0][graph_color]:#f38631
 * data[AuthorizationKey][authorization_key]
 *
 * @param Model $model Model using this behavior.
 * @param array $nc2Questionnaire Nc2Questionnaire data.
 * @return array Nc3Questionnaire data.
 */
	public function generateNc3QuestionnaireData(Model $model, $nc2Questionnaire) {
		$nc2QuestionnaireId = $nc2Questionnaire['Nc2Questionnaire']['questionnaire_id'];
		$questionnaireMap = $this->_getMap($nc2QuestionnaireId);
		if ($questionnaireMap) {
			// 既存の場合
			return [];
		}

		/* @var $Nc2ToNc3User Nc2ToNc3User */
		$Nc2ToNc3User = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3User');
		$data['Questionnaire'] = [
			'key' => Hash::get($questionnaireMap, ['Questionnaire', 'key']),
			'is_active' => '0',
			'status' => '3',
			'title' => $nc2Questionnaire['Nc2Questionnaire']['questionnaire_name'],
			'title_icon' => $this->_convertTitleIcon($nc2Questionnaire['Nc2Questionnaire']['icon_name']),
			'answer_timing' => '0',
			'is_no_member_allow' => $nc2Questionnaire['Nc2Questionnaire']['nonmember_flag'],
			'is_anonymity' => $nc2Questionnaire['Nc2Questionnaire']['anonymity_flag'],
			'is_key_pass_use' => $nc2Questionnaire['Nc2Questionnaire']['keypass_use_flag'],
			'is_total_show' => $nc2Questionnaire['Nc2Questionnaire']['total_flag'],
			'is_image_authentication' => $nc2Questionnaire['Nc2Questionnaire']['image_authentication'],
			'is_answer_mail_send' => $nc2Questionnaire['Nc2Questionnaire']['mail_send'],
			'is_page_random' => '0',
			'created_user' => $Nc2ToNc3User->getCreatedUser($nc2Questionnaire['Nc2Questionnaire']),
			'created' => $this->_convertDate($nc2Questionnaire['Nc2Questionnaire']['insert_time']),
		];
		if ($nc2Questionnaire['Nc2Questionnaire']['status'] != '0') {
			$data['Questionnaire']['is_active'] = '1';
			$data['Questionnaire']['status'] = '1';
		}
		if ($nc2Questionnaire['Nc2Questionnaire']['status'] != '2') {
			$data['Questionnaire'] += [
				'answer_timing' => '1',
				'answer_end_period' => $this->_convertDate($nc2Questionnaire['Nc2Questionnaire']['insert_time']),
			];
		}
		if ($nc2Questionnaire['Nc2Questionnaire']['keypass_use_flag'] == '1' &&
			$nc2Questionnaire['Nc2Questionnaire']['image_authentication'] == '1'
		) {
			$data['Questionnaire']['is_image_authentication'] = '0';
		}
		if ($nc2Questionnaire['Nc2Questionnaire']['questionnaire_type'] == '3') {
			$data['Questionnaire']['is_page_random'] = '1';
		}
		if ($nc2Questionnaire['Nc2Questionnaire']['keypass_use_flag'] == '1') {
			$data['AuthorizationKey']['authorization_key'] = $nc2Questionnaire['Nc2Questionnaire']['keypass_phrase'];
		}

		$data['QuestionnairePage'] = $this->__generateNc3QuestionnairePageData($nc2Questionnaire);

		// @see https://github.com/NetCommons3/Topics/blob/3.1.0/Model/Topic.php#L388-L393
		$data['Topic'] = [
			'plugin_key' => 'questionnaires'
		];

		return $data;
	}

/**
 * Generate Nc3QuestionnaireFrameSetting data.
 *
 * Data sample
 * data[QuestionnaireFrameSetting][id]:
 * data[QuestionnaireFrameSetting][frame_key]:4a5733f403efb04b89149453b2c3ead1
 * data[QuestionnaireFrameSetting][display_type]:1
 * data[QuestionnaireFrameSetting][display_num_per_page]:10
 * data[QuestionnaireFrameSetting][sort_type]:Questionnaire.modified DESC
 * data[Single][QuestionnaireFrameDisplayQuestionnaire][questionnaire_key]:0ba02955abaf89e75abd5308e518db21
 *
 * @param Model $model Model using this behavior.
 * @param array $nc2QBlock Nc2QuestionnaireBlock data.
 * @return array Nc3QuestionnaireFrameSetting data.
 */
	public function generateNc3QuestionnaireFrameSettingData(Model $model, $nc2QBlock) {
		$nc2QuestionnaireId = $nc2QBlock['Nc2QuestionnaireBlock']['questionnaire_id'];
		$questionnaireMap = $this->_getMap($nc2QuestionnaireId);
		if (!$questionnaireMap) {
			$message = __d('nc2_to_nc3', '%s does not migration.', $this->getLogArgument($nc2QBlock));
			$this->writeMigrationLog($message);
			return [];
		}

		/* @var $Nc2ToNc3Map Nc2ToNc3Map */
		$Nc2ToNc3Map = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3Map');
		$nc2BlockId = $nc2QBlock['Nc2QuestionnaireBlock']['block_id'];
		$mapIdList = $Nc2ToNc3Map->getMapIdList('QuestionnaireFrameSetting', $nc2BlockId);
		if ($mapIdList) {
			// 移行済み
			//return [];
		}

		/* @var $QFrameSetting QuestionnaireFrameSetting */
		$QFrameSetting = ClassRegistry::init('Questionnaires.QuestionnaireFrameSetting');
		$data = $QFrameSetting->getDefaultFrameSetting();
		$data['QuestionnaireFrameSetting']['id'] = Hash::get($mapIdList, [$nc2BlockId]);
		$data['QuestionnaireFrameSetting']['display_type'] = '0';
		$data['Single']['QuestionnaireFrameDisplayQuestionnaire'] = [
			'questionnaire_key' => $questionnaireMap['Questionnaire']['key']
		];

		return $data;
	}

/**
 * Get Log argument.
 *
 * @param array $nc2Questionnaire Array data of Nc2Questionnaire, Nc2CalendarBlock and Nc2CalendarPlan.
 * @return string Log argument
 */
	private function __getLogArgument($nc2Questionnaire) {
		if (isset($nc2Questionnaire['Nc2Questionnaire'])) {
			return 'Nc2Questionnaire ' .
				'questionnaire_id:' . $nc2Questionnaire['Nc2Questionnaire']['questionnaire_id'];
		}

		if (isset($nc2Questionnaire['Nc2QuestionnaireBlock'])) {
			return 'Nc2QuestionnaireBlock ' .
				'block_id:' . $nc2Questionnaire['Nc2QuestionnaireBlock']['block_id'];
		}
	}

/**
 * Generate Nc3CalendarFrameSettingSelectRoom data.
 *
 * @param array $nc2Questionnaire Nc2Questionnaire data.
 * @return array Nc3QuestionnairePage data.
 */
	private function __generateNc3QuestionnairePageData($nc2Questionnaire) {
		/* @var $Nc2Question AppModel */
		$Nc2Question = $this->_getNc2Model('questionnaire_question');
		$nc2Questions = $Nc2Question->findAllByQuestionnaireId(
			$nc2Questionnaire['Nc2Questionnaire']['questionnaire_id'],
			null,
			'question_sequence',
			null,
			null,
			-1
		);

		/* @var $Nc2ToNc3User Nc2ToNc3User */
		$Nc2ToNc3User = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3User');
		$data = [];
		$nc3PageSequence = 0;
		$nc3QuestionSequence = 0;
		foreach ($nc2Questions as $nc2Question) {
			$nc3Question = [];

			$nc2QuestionType = $nc2Question['Nc2QuestionnaireQuestion']['question_type'];
			$isNotTextType = ($nc2QuestionType != '2');
			$nc3Question = [
				'question_sequence' => $nc3QuestionSequence,
				'question_value' => '質問',	// TODOー英語の場合はQuestionにする
				'question_type' => $this->_convertQuestionType($nc2QuestionType),
				'description' => $nc2Question['Nc2QuestionnaireQuestion']['question_value'],
				'is_require' => $nc2Question['Nc2QuestionnaireQuestion']['require_flag'],
				// @see https://github.com/NetCommons3/Questionnaires/blob/3.1.0/Model/QuestionnaireQuestion.php#L232-L240
				'is_result_display' => $isNotTextType ? '1' : '0',
				'result_display_type' => '0',
				'created_user' => $Nc2ToNc3User->getCreatedUser($nc2Question['Nc2QuestionnaireQuestion']),
				'created' => $this->_convertDate($nc2Question['Nc2QuestionnaireQuestion']['insert_time']),
			];

			if ($isNotTextType) {
				$nc3Question['QuestionnaireChoice'] = $this->__generateNc3QuestionnaireChoiceData($nc2Question);
			}

			$data[$nc3PageSequence]['page_sequence'] = $nc3PageSequence;
			$data[$nc3PageSequence]['QuestionnaireQuestion'][$nc3QuestionSequence] = $nc3Question;

			if ($nc2Questionnaire['Nc2Questionnaire']['questionnaire_type'] == '1') {
				$nc3PageSequence++;
			} else {
				$nc3QuestionSequence++;
			}
		}

		return $data;
	}

/**
 * Generate Nc3CalendarFrameSettingSelectRoom data.
 *
 * @param array $nc2Question Nc2QuestionnaireQuestion data.
 * @return array Nc3QuestionnaireChoice data.
 */
	private function __generateNc3QuestionnaireChoiceData($nc2Question) {
		/* @var $Nc2Choice AppModel */
		$Nc2Choice = $this->_getNc2Model('questionnaire_choice');
		$nc2Choices = $Nc2Choice->findAllByQuestionId(
			$nc2Question['Nc2QuestionnaireQuestion']['question_id'],
			null,
			'choice_sequence',
			null,
			null,
			-1
		);

		/* @var $Nc2ToNc3User Nc2ToNc3User */
		$Nc2ToNc3User = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3User');
		$data = [];
		$nc3ChoiceSequence = 0;
		foreach ($nc2Choices as $nc2Choice) {
			$data[] = [
				'choice_sequence' => $nc3ChoiceSequence,
				'choice_label' => $nc2Choice['Nc2QuestionnaireChoice']['choice_value'],
				'graph_color' => $this->_getGraphColor($nc3ChoiceSequence),
				'created_user' => $Nc2ToNc3User->getCreatedUser($nc2Choice['Nc2QuestionnaireChoice']),
				'created' => $this->_convertDate($nc2Choice['Nc2QuestionnaireChoice']['insert_time']),
			];
			$nc3ChoiceSequence++;
		}

		return $data;
	}

/**
 * Get map
 *
 * @param array|string $nc2QuestionnaireIds Nc2CQuestionnaire questionnaire_id.
 * @return array Map data with Nc2Block block_id as key.
 */
	protected function _getMap($nc2QuestionnaireIds) {
		/* @var $Nc2ToNc3Map Nc2ToNc3Map */
		/* @var $Questionnaire Questionnaire */
		$Nc2ToNc3Map = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3Map');
		$Questionnaire = ClassRegistry::init('Questionnaires.Questionnaire');

		$mapIdList = $Nc2ToNc3Map->getMapIdList('Questionnaire', $nc2QuestionnaireIds);
		$query = [
			'fields' => [
				'Questionnaire.id',
				'Questionnaire.key',
			],
			'conditions' => [
				'Questionnaire.id' => $mapIdList
			],
			'recursive' => -1,
			'callbacks' => false,
		];
		$nc3Questionnaires = $Questionnaire->find('all', $query);
		if (!$nc3Questionnaires) {
			return $nc3Questionnaires;
		}

		$map = [];
		foreach ($nc3Questionnaires as $nc3Questionnaire) {
			$nc2Id = array_search($nc3Questionnaire['Questionnaire']['id'], $mapIdList);
			$map[$nc2Id] = $nc3Questionnaire;
		}

		if (is_string($nc2QuestionnaireIds)) {
			$map = $map[$nc2QuestionnaireIds];
		}

		return $map;
	}

}
