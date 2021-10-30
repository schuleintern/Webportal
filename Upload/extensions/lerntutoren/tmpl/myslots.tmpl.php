<div class="box">
	<div class="box-body">
		<div id="app">

            <?php if($HTML_tutoren_myslots_hinweis): ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Hinweise</h3>
                    </div>
                    <div class="box-body">
                        <?= $HTML_tutoren_myslots_hinweis ?>
                    </div>
                </div>
            <?php endif; ?>



            <?php if($isTutor): ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-user"></i> Meine Lernangebote</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped">
                            <tr>
                                <th>Status</th>
                                <th>Jahrgangsstufe</th>
                                <th>Fach</th>
                                <th>Stunden</th>
                                <th></th>
                            </tr>
                            <?= $mySlotsHTML ?>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($hasSlots): ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-user"></i> Gebuchte Nachhilfestunden</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped">
                            <tr>
                                <th>Status</th>
                                <th>Stunden</th>
                                <th>Fach</th>
                                <th>Jahrgang</th>
                                <th>Lerntutor</th>
                                <th></th>
                            </tr>
                            <?= $myBookedSlotsHTML ?>
                        </table>
                    </div>
                </div>
            <?php endif; ?>


        </div>
	</div>
</div>