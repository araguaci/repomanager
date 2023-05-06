<?php
$myrepo = new \Controllers\Repo();
$myplan = new \Controllers\Planification();
$mygroup = new \Controllers\Group('repo');
$mylogin = new \Controllers\Login();

/**
 *  Get repos groups list
 */
$repoGroupsList = $mygroup->listAllName();

/**
 *  Getting users email
 */
$usersEmail = $mylogin->getEmails();

/**
 *  Récupération de la liste des planifications en liste d'attente ou en cours d'exécution
 */
$planQueueList = $myplan->listQueue();
$planRunningList = $myplan->listRunning();
$planDisabledList = $myplan->listDisabled();

$planList = array_merge($planRunningList, $planQueueList, $planDisabledList);
array_multisort(array_column($planList, 'Date'), SORT_ASC, array_column($planList, 'Time'), SORT_ASC, $planList);

unset($mylogin);
