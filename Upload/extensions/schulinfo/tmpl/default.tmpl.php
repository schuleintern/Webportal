
<div class="row">
    <div class="col-md-4">
        <div class="box">
            <div class="box-header"><h3 class="box-title"><i class="fa fa-info-circle"></i> Schuldaten und Schuladresse</h3></div>
            <div class="box-body">
                <b><?= $schulinfo_name ?></b><br />
                <?= $schulinfo_name_zusatz ?><br />
                <?= $schulinfo_adresse1 ?><br />
                <?= $schulinfo_adresse2 ?><br />
                <?= $schulinfo_plz ?> <?= $schulinfo_ort ?><br />

                <i class="fa fa-phone"></i> <?= $schulinfo_telefon ?><br />
                <i class="fa fa-print"></i> <?= $schulinfo_fax ?><br />

                <i class="fa fa-envelope"></i> <?= $schulinfo_email ?><br />
                <i class="fa fa-link"></i> <a href="http://<?= $schulinfo_homepage ?>" target="_blank"><?= $schulinfo_homepage ?></a><br />
            </div>
        </div>
    </div>


    <?php if ( DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || $schulinfo_schulleiter_show ): ?>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header"><h3 class="box-title"><i class="fa fa-user"></i> Schulleitung</h3>
                    <?php if (!$schulinfo_schulleiter_show): ?>
                        <small>Nicht für Eltern und Schüler sichtbar</small>
                    <?php endif; ?>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <th style="width:40%">Name</th>
                            <th>&nbsp;</th>
                        </tr>
                        <?= $schulleitungHTML ?>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-md-4">
        <div class="box">
            <div class="box-header"><h3 class="box-title"><i class="fa fa-user"></i> Statistik</h3></div>
            <div class="box-body">
                <table class="table table-striped">
                    <tr>
                        <td style="width:40%"><b>Anzahl der Schüler/innen</b></td>
                        <td><?= $schuelerAnzahl ?></td>
                    </tr>
                    <tr>
                        <td style="width:40%"><b>Anzahl der Klassen</b></td>
                        <td><?= $klassenAnzahl ?><br />Im Schnitt <?= $schuelerProKlasse ?> Schüler/innen pro Klasse</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-6">


        <?php if (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || $schulinfo_lehrer_show ): ?>
            <div class="box">
                <div class="box-header"><h3 class="box-title"><i class="fa fa-user"></i> Lehrer</h3>
                    <?php if (!$schulinfo_lehrer_show ): ?>
                        <small>Nicht für Eltern und Schüler sichtbar</small>
                    <?php endif; ?>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <th>Kürzel</th>
                            <th style="width:40%">Name</th>
                            <th>&nbsp;</th>
                        </tr>
                        <?= $lehrerHTML ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>



        <?php if (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || $schulinfo_personalrat_show ): ?>
            <div class="box">
                <div class="box-header"><h3 class="box-title"><i class="fa fa-user"></i> Personalrat</h3>
                    <?php if ($schulinfo_personalrat_show ): ?>
                        <small>Nicht für Eltern und Schüler sichtbar</small>
                    <?php endif; ?>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <th style="width:40%">Name</th>
                            <th>&nbsp;</th>
                        </tr>
                        <?= $personalratHTML ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>




    <?php if (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || $schulinfo_faecher_show ): ?>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header"><h3 class="box-title"><i class="fa fa-user"></i> Fächer</h3>
                    <?php if (!$schulinfo_faecher_show ): ?>
                        <small>Nicht für Eltern und Schüler sichtbar</small>
                    <?php endif; ?>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <th style="width:40%">Fach</th>
                            <?php if (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || $schulinfo_fachlehrer_show ): ?>
                                <th>Fachlehrer
                                    <?php if (!$schulinfo_fachlehrer_show ): ?>
                                        <small>Nicht für Eltern und Schüler sichtbar</small>
                                    <?php endif; ?>

                                </th>
                            <?php endif; ?>

                            <?php if (DB::getSession()->isTeacher() || DB::getSession()->isAdmin() || $schulinfo_fachbetreuer_show ): ?>
                                <th>Fachbetreuer
                                    <?php if (!$schulinfo_fachbetreuer_show ): ?>
                                        <small>Nicht für Eltern und Schüler sichtbar</small>
                                    <?php endif; ?>
                                </th>
                            <?php endif; ?>
                        </tr>
                        <?= $facherHTML ?>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>