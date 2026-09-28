# [Priseo] [23.0.0] - Statuts des prix concurrents - Tableau de bord - Reprise de la ligne de version

Description : Première version publiée depuis la **1.1.0 d'août 2024**. Elle apporte la **gestion des statuts** sur les prix concurrents, un **tableau de bord** par montant HT, une boîte de confirmation avant suppression, et aligne la numérotation du module sur le reste du parc : le numéro majeur suit la version majeure de Dolibarr.

**Le module demande désormais Dolibarr 23 au minimum et 24 au maximum.**

## Nouvelles fonctionnalités et innovations

### Prix concurrents

* **Gestion des statuts** sur les prix concurrents, avec contrôle du statut à l'enregistrement.
* **Tableau de bord** des prix concurrents par montant HT.
* **Boîte de confirmation** avant la suppression d'une fiche.

## Améliorations & corrections

### Prix concurrents

* La création depuis une fiche produit ne rend plus une page blanche sur variable indéfinie.
* La liste est filtrée sur le produit courant et conserve son contexte.
* Le produit et la date du relevé sont bien transmis à la création.
* Les valeurs nulles sont contrôlées avant usage.
* L'enregistrement en ligne est fiabilisé, et le hook `getElementProperties` retourne l'ensemble des propriétés de la classe.

### Socle

* La signature de `create()` et celle de `setCategories()` sont alignées sur `SaturneObject`.
* Les clés de langue du prix concurrent sont générées depuis le nom de l'élément.

### Interface

* L'icône du module ne déborde plus dans les onglets et le pied de page.

### Conformité du paquet Dolistore

* Les quatre points d'entrée du module chargeaient l'environnement par une garde inversée, qui ne fait qu'une tentative : le contrôle de paquet du Dolistore refusait le zip. Ils utilisent désormais le `if / elseif / else` à deux tentatives — une pour le module à la racine de Dolibarr, une pour le module dans `custom`.

### Sécurité

* Ajout des `index.php` de protection dans les dossiers qui en manquaient, et suppression de fichiers inutilisés.

## Note sur la numérotation

Le module passe de `1.1.0` à `23.0.0`. Le descripteur déclarait déjà `21.0.0` sans que les tags suivent : cette version aligne enfin les deux, sur la convention du parc Evarisk où le numéro majeur correspond à la version majeure de Dolibarr visée.
