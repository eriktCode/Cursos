<?php
class Database {
    private $supabaseUrl = 'https://cohhdwrjbgimbnoragxf.supabase.co';
    private $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNvaGhkd3JqYmdpbWJub3JhZ3hmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTA1NDY0MTAsImV4cCI6MjA2NjEyMjQxMH0.aSY6ySSecID2gqORYQup5qqElQE3lv9WCMJv9qxMa8Q';

    public function getHeaders() {
        return [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json'
        ];
    }

    public function getBaseUrl() {
        return $this->supabaseUrl . '/rest/v1/';
    }
}
?>
