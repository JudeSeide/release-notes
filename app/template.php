<?php
$colors = [
    'Task'  => 'rgb(32, 133, 191)',
    'Story' => 'rgb(64,187,14)',
    'Bug'   => 'rgb(212, 10, 10)',
];
?>

<html>
<style>
    * {
        font-size: 14px;
        font-family: Arial, sans-serif;
    }

    p {
        margin: 0 0 2em 0;
    }

    p.task {
        margin: 7px 0;
    }

    .badge {
        border-radius: 13px;
        display: inline-block;
        color: white;
        padding: 3px 8px;
        background-color: #7a7a7a;
        font-size: 12px;
        font-weight: bold;
    }

    .badge.task {
        background-color: rgb(32, 133, 191);
    }

    .badge.story {
        background-color: rgb(64, 187, 14);
    }

    .badge.bug {
        background-color: rgb(212, 10, 10);
    }
</style>
<p>
    Hi all,
</p>

<?php if ($input->getOption('weeks') !== null): ?>
    <p>
        Find below the software development release notes for the
        last <?= $input->getOption('weeks') ?> weeks.
    </p>
<?php else: ?>

    <p>
        Find below the software development release notes
        since <?= \Carbon\Carbon::parse($input->getOption('date'))->format('M jS, Y') ?>.
    </p>
<?php endif ?>

<p>
    Release notes <?= date('M jS, Y'); ?>:
</p>
<?php
/** @var \chobie\Jira\Issue $issue */
foreach ($walker as $issue): ?>
    <p class="task">
        <a class="badge id <?= strtolower($issue->getIssueType()['name']) ?>"
           href="<?= $_ENV['JIRA_URL'].'/browse/'.$issue->getKey() ?>"
           title="<?= $issue->getIssueType()['name'] ?> <?= $issue->getKey() ?>">
            <?= $issue->getKey() ?>
        </a>
        <?php if ($issue->get('Epic Link')): ?>
            <a class="badge">
                <?= $this->getEpic($issue->get('Epic Link'))->get('summary') ?>
            </a>
        <?php endif ?>
        <span><?= $issue->getSummary() ?></span>
    </p>
<?php endforeach ?>
</html>