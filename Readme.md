# Plugin Immobilier WordPress

Ce plugin permet de gérer des programmes immobiliers et des terrains dans WordPress.

## Structure du Plugin

Le plugin est organisé selon une architecture orientée objet avec les dossiers suivants :

### Dossiers Principaux
- **assets/** : Contient les ressources CSS et JavaScript
  - css/ : Styles pour l'admin
  - js/ : Scripts pour l'admin
- **includes/** : Contient les classes PHP du plugin
  - admin/ : Gestion de l'interface d'administration
    - filters/ : Filtres personnalisés pour les vues administratives
  - post-types/ : Définition des types de contenu personnalisés
  - taxonomies/ : Définition des taxonomies

## Fonctionnalités

### Types de contenu personnalisés
- **Programmes Immobiliers** (`up_program_program`)
  - Gestion des programmes immobiliers
  - Informations détaillées sur chaque programme
  - Association avec les lots

- **Lots** (`up_program_lot`)
  - Gestion des lots individuels
  - Rattachement à un programme
  - Caractéristiques détaillées

### Taxonomies
- **Programme** (`up_program_taxonomy_program`)
  - Synchronisation automatique avec les programmes
  - Utilisée pour filtrer les lots

- **État** (`up_program_taxonomy_state`)
  - Vendu
  - Disponible
  - Optionné
  - Réservé
  - Autre

- **Étage** (`up_program_taxonomy_level`)
  - RDC
  - Plein pied
  - R+1 à R+7

- **Type de propriété** (`up_program_property_type`)
  - Appartement
  - Maison
  - Studio
  - Duplex
  - Triplex
  - Loft
  - Terrain
  - Local Commercial
  - Bureau
  - Parking

## Installation

1. Téléchargez le plugin
2. Décompressez-le dans le dossier `/wp-content/plugins/`
3. Activez le plugin dans le menu 'Extensions' de WordPress

## Version
1.0.0

## Auteur
GEHIN Nicolas
