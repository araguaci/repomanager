<?php
trait update {
    public function exec_update() {
        global $TEMP_DIR;
        global $OS_FAMILY;
        global $WWW_DIR;
        global $PID_DIR;

        /**
         *  Démarrage de l'opération
         *  On récupère en BDD l'ID du repo/section qu'on met à jour, afin de l'indiquer à startOperation
         */
        $this->repo->db_getId();

        /**
         *  Création d'une opération en BDD, on indique également si on a activé ou non gpgCheck et gpgResign
         *  Si cette fonction est appelée par une planification, alors l'id de cette planification est stockée dans $this->id_plan, on l'indique également à startOperation()
         */
        if ($this->type == 'manual') $this->startOperation(array('id_repo_target' => $this->repo->id, 'gpgCheck' => $this->repo->gpgCheck, 'gpgResign' => $this->repo->gpgResign));
        if ($this->type == 'plan')   $this->startOperation(array('id_repo_target' => $this->repo->id, 'gpgCheck' => $this->repo->gpgCheck, 'gpgResign' => $this->repo->gpgResign, 'id_plan' => $this->id_plan));

        /**
         *  Ajout du PID de ce processus dans le fichier PID
         */
        $this->log->addsubpid(getmypid());

        /**
         *  Lancement du script externe qui va construire le fichier de log principal à partir des petits fichiers de log de chaque étape
         */
        $steps = 6;
        exec("php ${WWW_DIR}/operations/logbuilder.php ${PID_DIR}/{$this->log->pid}.pid {$this->log->location} ${TEMP_DIR}/{$this->log->pid} $steps >/dev/null 2>/dev/null &");

        try {
            /**
             *  Etape 0 : Afficher le titre de l'opération
             */
            $this->log->steplog(0);
            if ($OS_FAMILY == "Redhat") file_put_contents($this->log->steplog, "<h3>MISE A JOUR D'UN REPO</h3>");
            if ($OS_FAMILY == "Debian") file_put_contents($this->log->steplog, "<h3>MISE A JOUR D'UNE SECTION DE REPO</h3>");        
            
            /**
             *  Récupère la source du repo si celle-ci est vide 
             *  ça peut être le cas lorsque l'opération est lancée par une planification
             */
            $this->repo->db_getSource();

            /**
             *  Etape 1 : Afficher les détails de l'opération
             */
            $this->log->steplog(1);
            $this->op_printDetails();
            /**
            *   Etape 2 : récupération des paquets
            */
            $this->log->steplog(2);
            $this->op_getPackages('update');
            /**
            *   Etape 3 : signature des paquets/du repo
            */
            $this->log->steplog(3);
            $this->op_signPackages();
            /**
            *   Etape 4 : Création du repo et liens symboliques
            */
            $this->log->steplog(4);
            $this->op_createRepo();
            /**
             *  Etape 5 : Archivage de l'ancien repo/section
             */
            $this->log->steplog(5);
            $this->op_archive();
            /**
            *   Etape 6 : Finalisation du repo (ajout en BDD et application des droits)
            */
            $this->log->steplog(6);
            $this->op_finalize('update');

            /**
             *  Passage du status de l'opération en done
             */
            $this->status = 'done';

        } catch(Exception $e) {
            $this->log->steplogError($e->getMessage()); // On transmets l'erreur à $this->log->steplogError() qui va se charger de l'afficher en rouge dans le fichier de log

            /**
             *  Passage du status de l'opération en erreur
             */
            $this->status = 'error';

            /**
             *  Cloture de l'opération
             */
            $this->log->closeStepOperation();
            $this->closeOperation();
            
            /**
             *  Cas où cette fonction est lancée par une planification : la planif attend un retour, on lui renvoie false pour lui indiquer qu'il y a eu une erreur
             */
            return false;
        }
        /**
         *  Cloture de l'opération
         */
        $this->log->closeStepOperation();
        $this->closeOperation();
    }
}
?>