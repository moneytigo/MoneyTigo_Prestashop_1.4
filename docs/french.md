### [<< Retour à la page précédente](index)

## Module de paiement MoneyTigo pour PrestaShop 1.4 & 1.5

Ce module de paiement vous permet d'accepter les paiements par carte de crédit via la solution de paiement MoneyTigo.com. (https://www.moneytigo.com).
Ce module de paiement est compatible avec toutes les versions de prestashop 1.4 et 1.5.

* Version du module: 1.1.1

# INSTALLATION

Pour installer le plugin MoneyTigo nous vous invitons d'abord de : [Télécharger l'archive du module en cliquant ici](https://github.com/moneytigo/MoneyTigo_Prestashop_1.4/releases/download/1.1.1/moneytigo-prestashop-14-to-15-v1.1.1.zip)

### Pour prestashop V1.4

* 1 - Accédez à votre tableau de bord PRESTASHOP
* 2 - Cliquez sur l'onglet "Module".
* 3 - Cliquez ensuite sur "Ajouter un module à partir de mon ordinateur".
* 4 - Choisissez le fichier téléchargé et cliquez sur "Charger le module".
* 5 - Ensuite, vous allez dans la section "Paiement" et recherchez "MoneyTigo" et cliquez sur "Installer".
* 6 - Vous retournez au même endroit section "Paiement" > "MoneyTigo" et cette fois-ci cliquez sur "Configurer".
* 7 - Remplissez votre clé API et votre clé SECRET (que vous trouverez dans votre espace MoneyTigo) puis cliquez sur "Mettre à jour la configuration".
* 7 - L'installation est terminée et fonctionnelle !

### Pour prestashop V1.5

* 1 - Accédez à votre tableau de bord PRESTASHOP
* 2 - Cliquez sur l'onglet "Module" > "Module"."
* 3 - Cliquez ensuite sur "Ajouter un module".
* 4 - Choisissez le fichier téléchargé et cliquez sur "Mettre ce module en ligne".
* 5 - Puis vous allez dans la section "Paiement" et recherchez "MoneyTigo" et cliquez sur "Installer". (Vous pouvez utiliser la barre de recherche)
* 6 - Vous retournez au même endroit section "Paiement" > "MoneyTigo" et cette fois-ci cliquez sur "Configurer".
* 7 - Remplissez votre clé API et votre clé SECRET (que vous trouverez dans votre espace MoneyTigo) puis cliquez sur "Mettre à jour la configuration".
* 7 - L'installation est terminée et fonctionnelle !


## NOTE IMPORTANTE EN CAS DE MISE À JOUR

Si vous installez une nouvelle version du module MoneyTigo, vous DEVEZ d'abord supprimer complètement l'ancien module de paiement MoneyTigo avant d'insérer le nouveau, sinon vous risquez d'avoir une instabilité.

## DEUXIÈME CHOSE IMPORTANTE

Ne téléchargez pas l'archive Github mais le paquet généré par MoneyTigo de la dernière version stable en suivant ce lien : [Télécharger l'archive du module en cliquant ici](https://github.com/moneytigo/MoneyTigo_Prestashop_1.4/releases/download/1.1.1/moneytigo-prestashop-14-to-15-v1.1.1.zip)

## MODE TEST

L'activation du mode test se fait directement dans la gestion de vos sites sur votre tableau de bord MoneyTigo.

(**Note:** Pour tester les transactions, n'oubliez pas de basculer votre site web (dans votre interface MoneyTigo, en mode test/démo) et de le basculer en production lorsque vos tests sont terminés.)

Si vous utilisez le mode test, vous devez utiliser les cartes de crédit virtuelles suivantes :
* **Paiement accepté** : Carte n° 4000 0000 0000 0002 , Expiration 12/22 , Cvv 123
* **Paiement refusé** : Carte n° 4000 0000 0000 0036 , Expiration 12/22, Cvv 123
* **(Les cartes virtuelles ne fonctionnent pas en mode production)**
