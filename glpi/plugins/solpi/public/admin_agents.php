<?php

declare(strict_types=1);

define('GLPI_ROOT', dirname(__DIR__, 4) . DIRECTORY_SEPARATOR);

if (!file_exists(GLPI_ROOT . 'inc/includes.php')) {
    echo "GLPI_ROOT not found";
    exit;
}

require_once GLPI_ROOT . 'inc/includes.php';
require_once __DIR__ . '/../inc/bootstrap.php';
use Session;
use Html;

// Require login
if (!Session::getLoginUserID()) {
  Session::checkLoginUser();
  exit;
}

// Require plugin admin/config rights
if (!(Session::haveRight('plugin_solpi', UPDATE) || Session::haveRight('config', UPDATE))) {
  Html::displayMessage("Permissão negada", true);
  exit;
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>SOLPI - Agent Installations</title>
  <style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ccc;padding:8px}</style>
</head>
<body>
  <h1>SOLPI - Pending Installations</h1>
  <div id="list">Carregando...</div>

  <script>
    async function loadPending(){
      const res = await fetch('/plugins/solpi/api/plugins/solpi/agent/pending');
      const json = await res.json();
      const div = document.getElementById('list');
      if(!Array.isArray(json)) { div.innerText = JSON.stringify(json); return; }
      let html = '<table><tr><th>ID</th><th>Site</th><th>URL</th><th>Created</th><th>Actions</th></tr>';
      for(const r of json){
        html += `<tr><td>${r.id}</td><td>${r.site_name}</td><td>${r.site_url||''}</td><td>${r.created_at||''}</td><td>`+
          `<button onclick="approve(${r.id})">Approve</button> `+
          `<button onclick="reject(${r.id})">Reject</button>`+
          `</td></tr>`;
      }
      html += '</table>';
      div.innerHTML = html;
    }

    async function approve(id){
      await fetch('/plugins/solpi/api/plugins/solpi/agent/approve', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
      loadPending();
    }

    async function reject(id){
      await fetch('/plugins/solpi/api/plugins/solpi/agent/reject', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
      loadPending();
    }

    loadPending();
  </script>
</body>
</html>
