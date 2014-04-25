<?php
//
//clear session variables to start new test
//
if(!session_start()){
	session_start();
}
unset($_SESSION['TotalQuestionTested']);
unset($_SESSION['qid']);
unset($_SESSION['Testid']);
unset($_SESSION['Testname']);
unset($_SESSION['TestedIDs']);
unset($_SESSION['initial']);
unset($_SESSION['Correct']);
unset($_SESSION['rqid']);
unset($_SESSION['raid']);
?>