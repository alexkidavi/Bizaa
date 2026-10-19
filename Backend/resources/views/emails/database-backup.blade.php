<p>Hello,</p>

<p>The Bizaa database backup has been generated successfully.</p>

<p>
    <strong>Backup file:</strong> {{ basename($fileName) }}<br>
    <strong>Generated:</strong> {{ now()->format('d M Y, H:i:s') }}
</p>

<p>The SQL backup is attached to this email.</p>

<p>Regards,<br>Bizaa Automated Backup System</p>
