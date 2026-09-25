<style>.modal { z-index: 1060 !important; } .modal-backdrop { z-index: 1050 !important; } .modal-dialog { pointer-events: auto; } .modal-content { pointer-events: auto; }</style>
<script>$(function(){ $('.modal').on('show.bs.modal',function(){ if(this.parentNode!==document.body) document.body.appendChild(this); }); });</script>
