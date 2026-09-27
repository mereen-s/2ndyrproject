<h2>Notifications &mdash; critical findings first</h2>
<?php foreach ($items as $n): ?>
<div class="card <?= $n['status']==='Unread'?'unread':'' ?>">
  <b><?= $n['status']==='Unread' ? '<span class="critical">CRITICAL</span>' : 'viewed' ?></b>
  &middot; <?= e($n['message']) ?> <span class="note">(<?= e($n['source']) ?> &middot; <?= e(time_ago($n['created_at'])) ?>)</span><br>
  <?php if ($n['status']==='Unread'): ?>
    <a class="btn" href="<?= BASE_URL ?>/index.php?page=notif_open&nid=<?= $n['notification_id'] ?>">Open record</a>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?= pagination_links($pages, $current, BASE_URL.'/index.php?page=notifications') ?>
<?php if (!$items): ?><p>No notifications.</p><?php endif; ?>
<p class="note">Opening an alert marks it as read and takes you to the patient's record.</p>
