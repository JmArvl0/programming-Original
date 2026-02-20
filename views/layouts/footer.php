    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<?php foreach ($inlineScripts ?? [] as $inlineScript): ?>
<script><?php echo $inlineScript; ?></script>
<?php endforeach; ?>
<?php foreach ($scripts ?? [] as $scriptPath): ?>
<script src="<?php echo htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endforeach; ?>
</body>
</html>
