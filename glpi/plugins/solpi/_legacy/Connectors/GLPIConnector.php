
<?php

class GLPIConnector {

    public function createTicket($title, $content, $priority = 3) {
        return true;
    }

    public function updateTicket($ticketId, $content) {
        return true;
    }

    public function closeTicket($ticketId) {
        return true;
    }
}
