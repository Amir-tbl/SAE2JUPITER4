@extends('base')

@section('header')
    <div class="container d-block">
        <h1 class="h1">À propos du projet</h1>
        <p class="mb-0 opacity-75">Solution de suivi de colis développée au département Informatique de l'IUT à Villetaneuse</p>
    </div>
@endsection

@section('content')

    <section>
        <div class="table-header">
            <h2>Objectif du Projet</h2>
            <p>Pourquoi ce projet existe</p>
        </div>

        <div class="p-4">
            <p class="mb-4 fs-5">
                Ce projet a été développé dans le cadre de la <strong>SAE 3.01</strong> pour répondre à un vrai problème rencontré par l'IUT : <strong>la perte et le mauvais suivi des colis</strong>. L'objectif est de simplifier la gestion des commandes et des livraisons pour tous les départements de l'établissement.
            </p>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="bg-primary text-white p-4 rounded shadow h-100">
                        <h5 class="fw-bold mb-3">Suivi en temps réel</h5>
                        <p class="mb-0 opacity-90">
                            Suivre chaque colis depuis la commande jusqu'à la réception finale pour éviter les pertes.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="bg-primary text-white p-4 rounded shadow h-100">
                        <h5 class="fw-bold mb-3">Gestion centralisée</h5>
                        <p class="mb-0 opacity-90">
                            Regrouper toutes les informations sur les commandes au même endroit pour faciliter la coordination.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="bg-primary text-white p-4 rounded shadow h-100">
                        <h5 class="fw-bold mb-3">Interface simple</h5>
                        <p class="mb-0 opacity-90">
                            Proposer un outil accessible à tous, quel que soit le niveau technique de l'utilisateur.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="bg-primary text-white p-4 rounded shadow h-100">
                        <h5 class="fw-bold mb-3">Solution pratique</h5>
                        <p class="mb-0 opacity-90">
                            Créer un outil qui répond vraiment aux besoins quotidiens de l'IUT.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="table-header">
            <h2>L'équipe</h2>
            <p>Les développeurs du projet</p>
        </div>

        <div class="p-4">
            <div class="alert alert-light border mb-4">
                <p class="mb-3">
                    Nous sommes <strong>six étudiants en BUT2 Informatique</strong> à l'IUT de Villetaneuse qui ont travaillé sur ce projet dans le cadre de la SAE 3.01. Le développement s'est fait avec <strong>Laravel</strong> pour le backend, <strong>Bootstrap</strong> pour l'interface, et <strong>MariaDB</strong> pour la base de données.
                </p>
                <p class="mb-0">
                    Le projet a nécessité de la collaboration entre nous pour gérer la base de données, développer les fonctionnalités, et créer l'interface.

            <h5 class="mb-4 fw-bold">Membres de l'équipe</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center py-3">
                            <h6 class="mb-0 fw-semibold">Soan MOREAU</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center py-3">
                            <h6 class="mb-0 fw-semibold">Weame EL MOUTTAQUI</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center py-3">
                            <h6 class="mb-0 fw-semibold">Yasmine AIT SALAH</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center py-3">
                            <h6 class="mb-0 fw-semibold">Myriam ABDELLAOUI</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center py-3">
                            <h6 class="mb-0 fw-semibold">Dimitar DIMITROV</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border shadow-sm">
                        <div class="card-body text-center py-3">
                            <h6 class="mb-0 fw-semibold">Megane MAZEKEM</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
