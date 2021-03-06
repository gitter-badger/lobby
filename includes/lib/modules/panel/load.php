<?php
if(!\Lobby::status("lobby.serve")){
  /**
   * For enabling access by \Lobby\Panel
   */
  require __DIR__ . "/class.panel.php";
  
  /**
   * Panel UI
   */
  if(!\Lobby::status("lobby.install")){
    \Lobby::addScript("superfish", "/includes/lib/modules/panel/lib/superfish.js");
    \Lobby::addStyle("panel", "/includes/lib/modules/panel/lib/panel.css");
    \Lobby::addScript("panel", "/includes/lib/modules/panel/lib/panel.js");
  }
  
  /**
   * Default Items provided by the module
   */
  \Lobby\Panel::addTopItem("netStatus", array(
    "html" => "<span id='net' title='Online'></span>",
    "position" => "right"
  ));
    
  \Lobby::hook("body.begin", function(){
    include __DIR__ . "/panel.ui.php";
  });
  \Lobby::hook("admin.body.begin", function(){
    include __DIR__ . "/panel.ui.php";
  });
}
