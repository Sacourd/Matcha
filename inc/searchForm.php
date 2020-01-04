
    <div class="row justify-content-center align-items-center m-auto">
        <div class="col-4 text-center d-xl-flex justify-content-xl-center">
                <?php if (is_null($alert)) { ?>
            <div class="card" style="opacity: 0.65;background-color: rgb(44,44,44);">
                <div class="card-body">
                    <h4 class="card-title" style="color: rgb(207,20,20);">Rechercher son partenaire</h4>

                    <form action="" method="get" target="_self">
                        <div style="margin-bottom: 21px;">

                            <div class="form-row">
                                <div class="col">
                                    <h6 class="text-muted mb-2">Distance maximale idéale</h6>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col">
                                    <select class="custom-select custom-select-sm" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" required="" name="distance" title="Les personnes au delà de cette distance ne seront pas affichés">
                                        <option value="5">5 km</option>
                                        <option value="10">10 km</option>
                                        <option value="20" selected>20 km</option>
                                        <option value="50">50 km</option>
                                        <option value="100">100 km</option>
                                        <option value="200">200 km</option>
                                        <option value="500">500 km</option>
                                    </select>
                                </div>
                            </div>

                        </div>



                        <div style="margin-bottom: 21px;">
                            <div class="form-row">
                                <div class="col">
                                    <h6 class="text-muted mb-2">Tranche d'âge</h6>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col">
                                    <select class="custom-select custom-select-sm" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" required="" name="age" title="La tranche d'âge recherchée">
                                        <option value="18_25" selected>18 - 25 ans</option>
                                        <option value="25_30">25 - 30 ans</option>
                                        <option value="30_45">30 - 45 ans</option>
                                        <option value="45_100">45+</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 21px;">
                            <div class="form-row">
                                <div class="col">
                                    <h6 class="text-muted mb-2">Trier par</h6>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col">
                                    <select class="custom-select custom-select-sm" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" name="order" required="" title="Trier par ordre d'affichage croissant">
                                        <option value="interest" selected="">Le plus de points communs</option>
                                        <option value="popularity">Le plus populaire</option>
                                        <option value="distance">Le plus proche</option>
                                        <option value="age">Âge croissant</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div style="margin-bottom: 21px;">
                            <div class="form-row">
                                <div class="col">
                                    <h6 class="text-muted mb-2">Ordre de tri</h6>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col">
                                    <select class="custom-select custom-select-sm" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" required="" name="tri" title="Ordre d'affichage des résultats">
                                        <option value="ASC" selected>Croissant</option>
                                        <option value="DESC">Décroissant</option>
                                    </select>
                                </div>
                            </div>
                        </div>



                        <button class="btn btn-outline-danger btn-block" type="submit">Retrouver son âme soeur</button>
                    </form>

                </div>
            </div>
                <?php } else echo $alert; ?>
        </div>
    </div>