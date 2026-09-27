<h2>Notifications &mdash; critical findings first</h2>
<?php foreach ($items as $n): ?>
<?php $unread = $n['status']==='Unread'; $isCrit = strpos($n['message'], 'CRITICAL') === 0; ?>
<div class="card <?= $unread ? 'unread' : '' ?>">
  <?php if ($isCrit): ?><span class="critical">CRITICAL</span> &middot;<?php endif; ?>
  <span class="<?= $unread ? 'chip amber' : 'chip green' ?>"><?= $unread ? 'Unread' : 'Viewed' ?></span>
  <div style="margin-top:6px"><?= e($n['message']) ?> <span class="note">(<?= e($n['source']) ?> &middot; <?= e(time_ago($n['created_at'])) ?>)</span></div>
  <a class="btn <?= $unread ? '' : 'secondary' ?>" href="<?= BASE_URL ?>/index.php?page=notif_open&nid=<?= $n['notification_id'] ?>"><?= $unread ? 'Open record' : 'Open again' ?></a>
</div>
<?php endforeach; ?>
<?= pagination_links($pages, $current, BASE_URL.'/index.php?page=notifications') ?>
<?php if (!$items): ?><p>No notifications.</p><?php endif; ?>
<p class="note">Opening an alert marks it as read and takes you to the patient's record. You can open it again at any time.</p>
