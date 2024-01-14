<div class="box">
	<div class="box-body">
		<div id="app" class="width-40vw">


            <?php if ($LANG_add_text): ?>
                <div class="si-hinweis">
                    <?= $LANG_add_text ?>
                </div>
            <?php endif; ?>

            <form class="si-form" action="<?= URL_SELF ?>&task=save" method="post" enctype="multipart/form-data">
                <ul>
                    <li>
                        <?php if ($LANG_form_text): ?>
                            <div class="si-hinweis">
                                <?= $LANG_form_text ?>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li>
                        <label>Fach</label>
                        <input type="text" name="fach" />
                    </li>
                    <li>
                        <label>Jahrgangstufe</label>
                        <input type="text" name="jahrgang" />
                    </li>
                    <li>
                        <label>Nachhilfestunden</label>
                        <label class="small">1 Stunde entspricht 60 Minuten</label>
                        <input type="text" name="einheiten" />
                    </li>
                    <li>
                        <button class="si-btn" ><i class="fas fa-plus-circle"></i> Angebote erstellen</button>
                    </li>
                </ul>
            </form>


        </div>
	</div>
</div>