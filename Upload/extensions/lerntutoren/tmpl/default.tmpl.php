<div class="box">
	<div class="box-body">
		<div id="app">

            <?php if ($disclaimer): ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Hinweise</h3>
                    </div>
                    <div class="box-body">
                        <?php echo $disclaimer; ?>
                    </div>
                </div>
            <?php endif; ?>


            <div class="box">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-graduation-cap"></i> Verfügbare Lerntutoren</h3>
                </div>
                <div class="box-body">

                    <table class="table table-striped">
                        <tr>
                            <th style="width: 30%">Name</th>
                            <th>Jahrgangsstufe</th>
                            <th>Fach</th>
                            <th>Stunden</th>
                            <th></th>
                        </tr>
                        <?php echo $slotsHTML; ?>

                    </table>
                </div>
            </div>

        </div>
	</div>
</div>