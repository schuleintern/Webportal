<div class="box">
    <div class="box-body">

        <div class="flex-row">
            <div class="flex-3 si-form si-list">
                <ul>
                    <li>
                        <label>Anzahl Kalender:</label>
                        <label class="padding-l-l"><?= $countKalender ?></label>
                    </li>
                    <li>
                        <label>Anzahl Events:</label>
                        <label class="padding-l-l"><?= $countEvents ?></label>
                    </li>
                </ul>
            </div>
            <div class="flex-2 padding-l-l padding-t-l">
                <div class="si-box">
                    <p>Kalender und Events vom Kalender-AllInOne Importieren</p>
                    <button class="si-btn" onClick="handlerImportAIO()">Importieren</button>
                </div>
                <div class="si-box">
                    <h3>ACHTUNG!</h3>
                    <button class="si-btn si-btn-red" onClick="handlerDelete()">Alles Löschen!</button>
                </div>
            </div>
        </div>



    </div>
</div>
<script>

    var handlerDelete = function () {
        if (confirm("Wirklich alle Events und Kalender Löschen?") == true) {
            window.location.href = "<?= URL_SELF ?>&task=deleteAllItems";
        }
    }

    var handlerImportAIO = function () {
        if (confirm("Wirklich alle Events und Kalender Importieren?") == true) {
            window.location.href = "<?= URL_SELF ?>&task=importFromAllinone";
        }
    }

</script>