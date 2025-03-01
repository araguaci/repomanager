<?php ob_start(); ?>

<h5>CREATE A NEW GROUP</h5>

<form id="newGroupForm" autocomplete="off">
    <input id="newGroupInput" type="text" class="input-medium" placeholder="Group name" />
    <button type="submit" class="btn-xxsmall-green" title="Add">+</button>
</form>

<br>

<?php
if (!empty($repoGroupsList)) : ?>
    <h5>CURRENT GROUPS</h5>

    <?php
    foreach ($repoGroupsList as $group) :
        /**
         *  Retrieve repos members and repos not members of the group
         */
        $reposIn = $mygroup->getReposMembers($group['Id']);
        $reposNotIn = $mygroup->getReposNotMembers();
        $reposInCount = count($reposIn); ?>

        <div class="table-container grid-fr-4-1 bck-blue-alt group-config-btn pointer" group-id="<?= $group['Id'] ?>">
            <div>
                <p><?= $group['Name'] ?></p>
                <p class="lowopacity-cst"><?= $reposInCount ?> repo<?= $reposInCount > 1 ? 's' : '' ?></p>
            </div>

            <div class="flex justify-end">
                <img src="/assets/icons/delete.svg" class="delete-group-btn icon-lowopacity" group-id="<?= $group['Id'] ?>" group-name="<?= $group['Name'] ?>" title="Delete <?= $group['Name'] ?> group" />
            </div>
        </div>

        <div class="group-config-div detailsDiv margin-bottom-5 hide" group-id="<?= $group['Id'] ?>">
            <form class="group-form" group-id="<?= $group['Id'] ?>" autocomplete="off">
                <div class="grid grid-fr-1-2 align-item-center column-gap-10">
                    <span>Name</span>
                    <input class="group-name-input" type="text" group-id="<?= $group['Id'] ?>" value="<?= $group['Name'] ?>" />

                    <span>Include repos</span>
                    <select class="group-repos-list" group-id="<?= $group['Id'] ?>" name="group-repos[]" multiple>
                        <?php
                        /**
                         *  Repos members of the group will be selected by default in the list
                         */
                        if (!empty($reposIn)) {
                            foreach ($reposIn as $repo) {
                                if ($repo['Package_type'] == 'rpm') {
                                    echo '<option value="' . $repo['repoId'] . '" selected>' . $repo['Name'] . '</option>';
                                }
                                if ($repo['Package_type'] == 'deb') {
                                    echo '<option value="' . $repo['repoId'] . '" selected>' . $repo['Name'] . ' ❯ ' . $repo['Dist'] . ' ❯ ' . $repo['Section'] . '</option>';
                                }
                            }
                        }

                        /**
                         *  Repos not members of the group will be unselected in the list
                         */
                        if (!empty($reposNotIn)) {
                            foreach ($reposNotIn as $repo) {
                                if ($repo['Package_type'] == 'rpm') {
                                    echo '<option value="' . $repo['repoId'] . '">' . $repo['Name'] . '</option>';
                                }
                                if ($repo['Package_type'] == 'deb') {
                                    echo '<option value="' . $repo['repoId'] . '">' . $repo['Name'] . ' ❯ ' . $repo['Dist'] . ' ❯ ' . $repo['Section'] . '</option>';
                                }
                            }
                        } ?>
                    </select>
                </div>

                <br>
                <button type="submit" class="btn-large-green" title="Save">Save</button>
            </form>
        </div>
        <?php
    endforeach;
endif; ?>

<?php
$content = ob_get_clean();
$slidePanelName = 'repos/groups';
$slidePanelTitle = 'REPOS GROUPS';

include(ROOT . '/views/includes/slide-panel.inc.php');
