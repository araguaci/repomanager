<?php
trait duplicate {
    /**
     *  DUPLIQUER UN REPO/SECTION
     */
    public function exec_duplicate() {
        global $REPOS_DIR;
        global $WWW_USER;
        global $WWW_HOSTNAME;
        global $OS_FAMILY;
        global $GPGHOME;
        global $GPG_KEYID;

        ob_start();

        /**
         *  1. Génération du tableau récapitulatif de l'opération
         */
        if ($OS_FAMILY == "Redhat") echo "<h3>DUPLIQUER UN REPO</h3>";
        if ($OS_FAMILY == "Debian") echo "<h3>DUPLIQUER UNE SECTION DE REPO</h3>";

        echo '<table class="op-table">';
        if ($OS_FAMILY == "Redhat") {
            echo "<tr>
                <th>Nom du repo source :</th>
                <td><b>{$this->repo->name}</b> ".envtag($this->repo->env)."</td>
            </tr>";
        }
        if ($OS_FAMILY == "Debian") {
            echo "<tr>
                <th>Nom du repo source :</th>
                <td><b>{$this->repo->name}</b></td>
            </tr>
            <tr>
                <th>Distribution :</th>
                <td><b>{$this->repo->dist}</b></td>
            </tr>
            <tr>
                <th>Section de repo :</th>
                <td><b>{$this->repo->section}</b> ".envtag($this->repo->env)."</td>
            </tr>";
        }
        if (!empty($this->repo->newName)) {
            echo "<tr>
                <th>Nom du nouveau repo :</th>
                <td><b>{$this->repo->newName}</b></td>
            </tr>";
        }
        if (!empty($this->repo->description)) {
            echo "<tr>
                <th>Description :</th>
                <td><b>{$this->repo->description}</b></td>
            </tr>";
        }
        if (!empty($this->repo->group)) {
            echo "<tr>
                <th>Ajout à un groupe :</th>
                <td><b>{$this->repo->group}</b></td>
            </tr>";
        }
        echo '</table>';

        $this->log->steplog(1);
        $this->log->steplogInitialize('duplicate');
        $this->log->steplogTitle('DUPLICATION');
        $this->log->steplogLoading();

        /** 
         *  2. On vérifie que le nouveau nom du repo n'est pas vide
         */
        if (empty($this->repo->newName)) throw new Exception('le nom du nouveau est vide');

        /**
         *  3. Vérifications : 
         *  On vérifie que le repo/section source (celui qui sera dupliqué) existe bien
         *  On vérifie que le nouveau nom du repo n'existe pas déjà
         */
        if ($OS_FAMILY == "Redhat") {
            if ($this->repo->exists($this->repo->name) === false) throw new Exception("le repo à dupliquer n'existe pas");
        }
        if ($OS_FAMILY == "Debian") {
            if ($this->repo->section_exists($this->repo->name, $this->repo->dist, $this->repo->section) === false) throw new Exception("le repo à dupliquer n'existe pas");
        }
        if ($this->repo->exists($this->repo->newName) === true) throw new Exception("un repo <b>{$this->repo->newName}</b> existe déjà");

        /**
         *  4. On récupère la date et la source du repo qu'on va dupliquer
         */
        $this->repo->db_getDate();

        if ($OS_FAMILY == "Redhat") {
            // Source
            $resultSource = $this->db->querySingleRow("SELECT Source FROM repos WHERE Name = '{$this->repo->name}' AND Env = '{$this->repo->env}' AND Status = 'active'");
            // Signature 
            $resultSigned = $this->db->querySingleRow("SELECT Signed FROM repos WHERE Name = '{$this->repo->name}' AND Env = '{$this->repo->env}' AND Status = 'active'");
        }
        if ($OS_FAMILY == "Debian") {
            // Source
            $resultSource = $this->db->querySingleRow("SELECT Source FROM repos WHERE Name = '{$this->repo->name}' AND Dist = '{$this->repo->dist}' AND Section = '{$this->repo->section}' AND Env = '{$this->repo->env}' AND Status = 'active'");
            // Signature
            $resultSigned = $this->db->querySingleRow("SELECT Signed FROM repos WHERE Name = '{$this->repo->name}' AND Dist = '{$this->repo->dist}' AND Section = '{$this->repo->section}' AND Env = '{$this->repo->env}' AND Status = 'active'");
        }
        $this->repo->source = $resultSource['Source'];
        $this->repo->signed = $resultSigned['Signed'];

        /**
         *  4. Création du nouveau répertoire avec le nouveau nom du repo :
         */
        if ($OS_FAMILY == "Redhat") {
            if (!file_exists("${REPOS_DIR}/{$this->repo->dateFormatted}_{$this->repo->newName}")) {
                if (!mkdir("${REPOS_DIR}/{$this->repo->dateFormatted}_{$this->repo->newName}", 0770, true)) throw new Exception("impossible de créer le répertoire du nouveau repo <b>{$this->repo->newName}</b>");
            }
        }
        if ($OS_FAMILY == "Debian") {
            if (!file_exists("${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}")) {
                if (!mkdir("${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}", 0770, true)) throw new Exception("impossible de créer le répertoire du nouveau repo <b>{$this->repo->newName}</b>");
            }
        }

        /**
         *  5. Copie du contenu du repo/de la section
         *  Anti-slash devant la commande cp pour forcer l'écrasement
         */
        if ($OS_FAMILY == "Redhat") exec("\cp -r ${REPOS_DIR}/{$this->repo->dateFormatted}_{$this->repo->name}/* ${REPOS_DIR}/{$this->repo->dateFormatted}_{$this->repo->newName}/", $output, $result);
        if ($OS_FAMILY == "Debian") exec("\cp -r ${REPOS_DIR}/{$this->repo->name}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/* ${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/", $output, $result);
        if ($result != 0) throw new Exception('impossible de copier les données du repo source vers le nouveau repo');

        /**
         *   6. Création du lien symbolique
         */
        if ($OS_FAMILY == "Redhat") exec("cd ${REPOS_DIR}/ && ln -sfn {$this->repo->dateFormatted}_{$this->repo->newName}/ {$this->repo->newName}_{$this->repo->env}", $output, $result);            
        if ($OS_FAMILY == "Debian") exec("cd ${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/ && ln -sfn {$this->repo->dateFormatted}_{$this->repo->section}/ {$this->repo->section}_{$this->repo->env}", $output, $result);
        if ($result != 0) throw new Exception('impossible de créer le nouveau repo');

        /**
         *  7. On re-crée le repo avec les nouvelles informations (nouveau nom) et on resigne le repo avec GPG (Release.gpg). On fait ça uniquement sur Debian car avec Redhat/CentOS, ce sont les paquets qui sont 
         *  signés, donc cela n'a pas d'incidence si le nom du repo a changé
         */
        if ($this->repo->signed == "yes" OR $this->repo->gpgResign == "yes") {
            if ($OS_FAMILY == "Debian") {
                // On va utiliser un répertoire temporaire pour travailler
                $TMP_DIR = '/tmp/deb_packages';
                mkdir($TMP_DIR, 0770, true);
                // On se mets à la racine de la section
                // On recherche tous les paquets .deb et on les déplace dans le répertoire temporaire
                exec("cd ${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/ && find . -name '*.deb' -exec mv '{}' $TMP_DIR \;");
                // Après avoir déplacé tous les paquets on peut supprimer tout le contenu de la section
                exec("rm -rf ${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/*");
                // Création du répertoire conf et des fichiers de conf du repo
                mkdir("${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/conf", 0770, true);
                // Création du fichier "distributions"
                if (!file_put_contents("${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/conf/distributions", "Origin: Repo {$this->repo->newName} sur ${WWW_HOSTNAME}\nLabel: apt repository\nCodename: {$this->repo->dist}\nArchitectures: i386 amd64\nComponents: {$this->repo->section}\nDescription: Miroir du repo {$this->repo->newName}, distribution {$this->repo->dist}, section {$this->repo->section}\nSignWith: ${GPG_KEYID}\nPull: {$this->repo->section}".PHP_EOL)) {
                    throw new Exception('impossible de créer le fichier de configuration du repo (distributions)');
                }
                // Création du fichier "options"
                if (!file_put_contents("${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/conf/options", "basedir ${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}\nask-passphrase".PHP_EOL)) {
                    throw new Exception('impossible de créer le fichier de configuration du repo (options)');
                }

                // Création du repo en incluant les paquets deb du répertoire temporaire, et signature du fichier Release
                exec("cd ${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}/ && /usr/bin/reprepro --gnupghome ${GPGHOME} includedeb {$this->repo->dist} ${TMP_DIR}/*.deb", $output, $result);
                exec("rm -rf '$TMP_DIR'"); // Suppression du répertoire temporaire

                if ($result != 0) {
                    /**
                     *  Suppression de ce qui a été fait :
                     */
                    exec("rm -rf '${REPOS_DIR}/{$this->repo->newName}/{$this->repo->dist}/{$this->repo->dateFormatted}_{$this->repo->section}'");
                    exec("rm -rf $TMP_DIR");

                    throw new Exception("la signature de la section <b>{$this->repo->section}</b> du repo <b>{$this->repo->newName}</b> a échouée");
                }
            }
        }

        /**
         *  8. Insertion en BDD du nouveau repo
         */
        if ($OS_FAMILY == "Redhat") $this->db->exec("INSERT INTO repos (Name, Source, Env, Date, Time, Description, Signed, Type, Status) VALUES ('{$this->repo->newName}', '{$this->repo->source}', '{$this->repo->env}', '{$this->repo->date}', '{$this->repo->time}', '{$this->repo->description}', '{$this->repo->signed}', 'mirror', 'active')");
        if ($OS_FAMILY == "Debian") $this->db->exec("INSERT INTO repos (Name, Source, Dist, Section, Env, Date, Time, Description, Signed, Type, Status) VALUES ('{$this->repo->newName}', '{$this->repo->source}', '{$this->repo->dist}', '{$this->repo->section}', '{$this->repo->env}', '{$this->repo->date}', '{$this->repo->time}', '{$this->repo->description}', '{$this->repo->signed}', 'mirror', 'active')");

        $this->repo->id = $this->db->lastInsertRowID();

        /**
         *  9. Application des droits sur le nouveau repo créé
         */
        exec("find ${REPOS_DIR}/{$this->repo->newName}/ -type f -exec chmod 0660 {} \;");
        exec("find ${REPOS_DIR}/{$this->repo->newName}/ -type d -exec chmod 0770 {} \;");
        exec("chown -R ${WWW_USER}:repomanager ${REPOS_DIR}/{$this->repo->newName}/");

        $this->log->steplogOK();

        /**
         *  10. Ajout de la section à un groupe si un groupe a été renseigné
         */
        if (!empty($this->repo->group)) {
            $this->log->steplog(2);
            $this->log->steplogInitialize('addToGroup');
            $this->log->steplogTitle('AJOUT A UN GROUPE');
            $this->log->steplogLoading();

            if ($OS_FAMILY == "Redhat") $result = $this->db->query("SELECT repos.Id AS repoId, groups.Id AS groupId FROM repos, groups WHERE repos.Name = '{$this->repo->newName}' AND repos.Status = 'active' AND groups.Name = '{$this->repo->group}'");
            if ($OS_FAMILY == "Debian") $result = $this->db->query("SELECT repos.Id AS repoId, groups.Id AS groupId FROM repos, groups WHERE repos.Name = '{$this->repo->newName}' AND repos.Dist = '{$this->repo->dist}' AND repos.Section = '{$this->repo->section}' AND repos.Status = 'active' AND groups.Name = '{$this->repo->group}'");

            while ($data = $result->fetchArray()) {
                $repoId = $data['repoId'];
                $groupId = $data['groupId'];
            }

            if (empty($this->repo->id)) throw new Exception("impossible de récupérer l'id du repo <b>{$this->repo->newName}</b>");
            if (empty($groupId)) throw new Exception("impossible de récupérer l'id du groupe <b>{$this->repo->group}</b>");

            $this->db->exec("INSERT INTO group_members (Id_repo, Id_group) VALUES ('$repoId', '$groupId')");

            $this->log->steplogOK();
        }

        /**
         *  11. Génération du fichier de conf repo en local (ces fichiers sont utilisés pour les profils)
         *  Pour les besoins de la fonction, on set $this->repo->name = $this->repo->newName (sinon ça va générer un fichier pour le repo source, ce qu'on ne veut pas)
         */
        $this->repo->name = $this->repo->newName;
        $this->repo->generateConf('default');
    }
}
?>