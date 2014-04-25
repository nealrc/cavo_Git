<?php
//
//clear session variables to start new test
//
if(!session_start()){
	session_start();
}
unset($_SESSION['TotalQuestionsRequired']);
unset($_SESSION['TotalQuestionTested']);
unset($_SESSION['LevelQuestionTested']);
unset($_SESSION['Level']);
unset($_SESSION['Stage']);
unset($_SESSION['qid']);
unset($_SESSION['Starttime']);
unset($_SESSION['Endtime']);
unset($_SESSION['Duration']);

unset($_SESSION['Testid']);
unset($_SESSION['Testtype']);

unset($_SESSION['TestedIDs']);
unset($_SESSION['initial']);
unset($_SESSION['testidentifer']);
?>
