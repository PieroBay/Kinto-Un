# Kinto'un [Framework]
v 1.7.1

Nécessite PHP 5.4 ou +

[Utilise le template Twig/Smarty ou Php classique] 


## Installation

### 1. Installation avec Composer:

Lancer une des commandes: 

* `php composer.phar create-project --prefer-dist kintoun/kintoun [app_name]`
* `composer create-project --prefer-dist kintoun/kintoun [app_name]`


### 2. Installation depuis zip:

Extraire le zip à la racine de votre site et renomer le dossier.
Si vous déplacez le contenu du dossier, /!\ N'oubliez pas le `.htaccess` !

### Composer

**/!\ Faire un update de Composer pour installer les templates où le framework ne fonctionnera pas /!\** 

(l'installation avec Composer télécharge automatiquement les fichiers nécessaires).

plus d'info sur Composer => `https://getcomposer.org/doc/00-intro.md`

Par défault, le template est Twig.
Pour utiliser Smarty, le rajouter dans `composer.json`.

## Fichier config

Le fichier de config `Config.yml` se trouvant à `app/config/Config.yml` vous permettra de: 

* Vous connecter à la DB, de choisir le template (none/php, Twig ou Smarty), de choisir la langue local...

* Générer un token qui sera crypté ensuite pour sécuriser votre site de la faille CSRF et choisir le délais (en minute) d'expiration du token. (optionel, vous pouvez laisser les deux champs vide). 

* Configurer vos connexions. (plus d'info dans la section Connexion).


## Project

Les projets sont des dossiers contenant un enssemble de code permettant par la suite d'être réutilisé plus facilement.

Les projets sont à placer dans le dossier `src/project`.
Un projet se compose:
![image](http://img11.hostingpics.net/pics/969961folder.png)

A l'ajout d'un nouveau projet, n'oubliez pas de l'ajouter dans le routing se trouvant dans `app/config/routing.yml`


## Controller

Les controllers se trouvent dans `src/project/*votre projet*/controller`.

Les noms de vos controller auront le même nom que les dossiers contenant vos vues dans le dossier `views`.

Dans les Controller, créez des actions. Le nom des actions auront le même nom que les pages dans les vues **(sans 'Action' à la fin)**.

![image](http://img15.hostingpics.net/pics/932424action.png)


### Récuperer des données de la DB

Dans le controller, dans le tableau `$table`, séparez par des virgules les tables nécessaires pour les actions.

![image](http://imageshack.com/a/img541/8637/c9ef.png)

Un model principal avec des requetes de base est par défaut dans le framework.

Si vous désirez des requètes personelles, créez un fichier table1Model.php (prennons l'exemple de la photo du dessus, table1 est le nom de la table) dans le dossier `models`.
Dans ce fichier, créez une classe du même nom que le fichier (table1Model.php) qui extends de `Model`.

```php
<?php
class table1Model extends Model{

	public function enregistrementPersonnel(){
		// requète(s)
	}
}
?>
```

Pour utiliser ces requetes dans le controller, utilisez `$this->table1Model->enregistrementPersonnel()`.

Requètes principales:

* `$this->table1->delete($data)` => DELETE FROM $table WHERE $data. Si $data est int, il supprimera via l'id, sinon justifier le where en array. `array("user"=>"Marc")`

* `$this->table1->findAll(array())->exec()` => Dans le tableau, mettre des conditions => "where", "fields", "limit", "order"

* `$this->table1->findOne($where)->exec()` => $where = array("name"=>"john","surname"=>"Doe"). Retournera un objet.

* `$this->table1->find($request)->exec()` => $request = array("name"=>"john","surname"=>"Doe"). Retournera un objet.

* `$this->table1->findById($id)->exec()` => SELECT * FROM $table WHERE id = $id

* `$this->table1->save($_POST, $upload)` => $_POST ou array en paramètre => si le tableau contient un id, un UPDATE sera fait sinon un INSERT. 
$upload (non obligatoire si pas d'upload dans le formulaire) est un array avec plusieurs parametres.

Si une table a besoin d'une liaison, après recupération des données avec "finAll()","findOne()","find()","findById()", utiliser "->link(array("key","as","from"))".


* key => Le champ des données récupéré à lier
* as => Le champ de la table à lier aux données récupérées
* from => La table à lier.

Le nom du champ 'key' sera remplacé par 'form'.


### Exemple

```php
	# return $this->foo->findAll()->exec();

stdClass Object
(
    [id] => 1
    [name] => Kinto
    [id_tag] => 3
    [id_picture] => 9
)

	# return $this->foo->findAll()->link("id_tag","id","tag")->link("id_picture","id","picture")->exec();

stdClass Object
(
    [id] => 1
    [name] => Kinto
    [tag] => stdClass Object
        (
            [id] => 1
            [name] => Framework
        )
    [picture] => stdClass Object
        (
            [id] => 1
            [name] => Yellow Cloud
            [link] => http://...
        )
)
?>
```
 

/!\ Ne pas oublier `enctype="multipart/form-data"` dans la balise form.

```php
array(
			"target"      =>  "folder_name", "folder_name", # le dossier sera dans "src/ressources/images/"
			"table_name"  =>  "image", # nom de la table ou les url des images seront enregistrées
			"champ_name"  =>  "image",  # nom du champ dans de la liaison de la table images
			"maxWeight"   =>  2097152, # poids max en byte
			"size"  	  =>  ['1000x1000'],  # taille max en pixel.
			"edit"		  =>	 "add", # add ou replace. Si à l'ajout ca enlève l'ancien fichier ou il s'ajoute
			"ext"         =>  array('jpg','png','jpeg'), # extensions autorisées
			"resize"      =>  false,  # Si l'image doit être redimensionner mettre une taille en pixel, sinon laisser vide
```

* `$this->table1->allOk()` => uniquement pour vérifier si l'upload s'est déroulé correctement.
```php
if($this->table1->allOk()){
	// ok
}else{
	echo $this->table1->getError(); # affiche le message d'erreur
}
```

* `$this->table1->lastId()` ($this->table->lastId()) => Récupere le dernier id ajouté si un save() a été utilisé juste avant

* `if($_POST){}` => test si un post a été fait

* `if($this->is_valid){}` => vérifie si le token du formulaire est valide (contrer la faille CSRF) et vérifie si il y a un $_POST.

##### Champs hidden token
Pour ajouter votre token dans vos formulaires, ajoutez la fonction Twig/Smarty `{{ CSRF_TOKEN() }}` avant votre submit.


### Envoyer des données de la DB à la vue

Dans les actions, quand les données sont récupérées, on les envoie à la vue grâce à la function render() avec un tableau en paramètre.
`$this->render(array())`

*Exemple:*

```php
$this->render(array(
	'message' => $listes_message,
));
```


### Message Flash

Créez un message Flash `$this->Session->setFlash('error', 'Mauvais identifiant');` (le type du message en premier paramètre)

envoyer le message à la vue:
```php
$this->render(array(
	"flash"	=>	$this->Session->flash(),
));
```

Recuperer le message dans la vue twig: `{{ flash|raw }}` , renvoie un div `<div class="flash flash-'type'">message</div>` (type = error ou ok).

### Redirection

`$this->redirectUrl('home_index', array("id"=>5,"slug"=>"foo"));` 

* `home_index` est le nom de la route vers laquelle vous voulez rediriger (la route des projets et non celle de la config);
* `array` Si votre route a besoin de paramètre, indiquez les du même nom que les variables `{...}` qui se trouve dans `routing.yml` dans ce tableau;

### Envoi de mail

`$this->sendMail->send($to,$fromName,$fromMail,$subject,$message);`

(Les messages peuvent contenir de l'html)

### ROLE 

Dans la table qui contient les utilisateurs, ajoutez un champs `role` pour pouvoir gerer les droits d'accès.

Tout visiteur est automatiquement authentifier en tant que ROLE **'visiteur'**.

Pour limiter l'accès à certaines pages, utiliser: 	

```php
if(!$this->ROLE('admin')){ // exemple, la page est limitée aux membres qui ont comme ROLE admin.
  $this->redirectUrl('public:index.html.twig'); // ceux qui ne sont pas admin, ils seront redirigé sur l'index.
}
```

Par défaut, `$this->ROLE()` contient 'visiteur' et return true. 

### Connexion

Un champs `role` doit être créé dans la table.
Par défaut une session role visiteur est créée.

Pour configurer une connexion, dans le fichier `config.yml` se trouvant à `core/config.yml` remplissez les champs demandé:

```yml
connection:
    login: name 			# Champs login du formulaire qui a le même nom que dans votre table
    password: password		# Champs password du formulaire qui a le même nom que dans votre table
    remember: false			# Si vous voulez que le navigateur se rappel des identifians de l'utilisateur (true ou false)
    activation: true 		# Si une vérification est nécessaire pour voir si le compte est actif
    session: id|nom			# La liste des sessions une fois l'utilisateur connecté. séparer par des pipes `|`
```

###### Récupérer les cookies si true
* $_COOKIE['ku_login']
* $_COOKIE['ku_password']


Utilisez `$this->table->connexion($_POST);` dans une action au nom de votre choix pour un connexion.

`$this->user->testConnect()`, si tout est ok, return true

```php
$this->user->connexion($_POST); // envoie le formulaire
if($this->user->testConnect()){ // si la connexion s'est bien passée, return true
  $this->redirectUrl('public:choice.html.twig'); // on redirige
}else{
	$this->Session->setFlash('error', 'Mauvais identifiant'); // sinon on envoie un message flash
}
```

Créera automatiquement une session ROLE qui sera repris de la DB.

### Deconnexion

Utilisez le code ci dessous dans une action au nom de votre choix pour une déconnexion.

```php
$this->user->deconnexion(); // modifie le ROLE actuel en 'visiteur' et supprime les autres sessions
$this->redirectUrl('home_index'); // ensuite, redirection sur l'index
```

## Views

Les vues sont dans le dossier `views`.

Les pages ont les mêmes noms que les actions (sans 'Action' à la fin) et sont placées dans le dossier des controllers et ont comme extension `.html.twig` (si Twig) `.tpl` (si Smarty) ou `.php` (si aucun template).

Si vous avez choisi le php classique, récupérez les données dans la vue avec `<?php echo $data['foo']; ?>` .

### Ressources
Les ressources Css/Js/Images/fonts, sont à placer dans `src / ressources`, chacun sont dans un dossier respectif.

![image](http://img4.hostingpics.net/pics/990630ressources.png)

Dans les vues, le lien pour accèder aux ressources sera

`href="{{ Info.Ressources }}css/style.css"`

## Routing

Le fichier de config de routing se trouve à `src/project/*VOTRE PROJET*/config/routing.yml`.

```yml
view_article:
    pattern:  /{_lang}/article/{slug}_{id}/{_name}
    controller: home:public:view
```

* `view_article` => Est le nom de la route, qui sera utile pour la fonction path().
* `pattern` => Est le lien subjectif désiré avec ses paramètres.
* `controller` => Est le chemin vers le project/controller/action. 

Les parametres avec un underscore "_" peuvent être absent dans l'url et dans la génération de lien `path()` et à la redirection `redirectUrl()`.

{_lang} permet de récupérer la langue dans l'url et peut être absent dans l'url.

### lien

Dans les vues, une fonction twig permet de créer des liens dynamiquement.
`<a href="{{path("view_article",{"slug": "mon-beau-slug", "id": "9"})}}">cliquez ici</a>`
le parametre {_lang} n'est pas obligatoire, si il est vide, il ajoutera automatiquement la langue de la session.
le nom de la route est celle qui se trouve dans le fichier config de votre projet et non la route du dossier config.

retournera:
`<a href="_ROOT_/_session-langue_/article/mon-beau-slug_9">cliquez moi</a>`

## Multilangage

Kinto'Un permet de traduire son site très facilement.

Pour se faire, créez un fichier `.yml` dans `src/ressources/translate/` et nommez le à la langue que vous souhaitez traduire (2 lettres).
Si mon site est en Français et je veux le traduire en anglais, je nomerai le fichier `en.yml`.

Dans ce fichier, écrivez d'un coté la sentence cible (la langue de votre site) ensuite vous mettez deux points `:` et la sentence à la langue que vous voulez traduire.

```yml
Bonjour: Hello
Comment allez vous: How are you
"Numéro de téléphone:": Phone number: 
```

Dans la vue, pour traduire vos sentences, les manières varient selon le template utilisé:

* Twig => `{{"Bonjour"|trans}}`
* Smarty => `{"Bonjour"|trans}`
* Php => `<?= trans("Bonjour"); ?>`


## Créez vos filtres/fonctions

Créez votre fichier php dans le dossier `libs/template/extensions/`.
Danse ce fichier, créez votre fonction qui servira de filtre ou de fonction.
Votre fichier doit avoir le même nom que votre fonction et se terminer par `-function` ou `-filter` et ce nom servira comme filtre/fonction pour tout les templates.

## Personnalisez la page 404 et les autres pages d'erreur

Un template de base est situé à `core/errors/error.html.twig` et les ressources sont dans le dossier `ressources`.
Le controller `errorController.php` se trouvant à `core/errors/errorController.php` vous permet de rendre votre page dynamique.


## XML

Générer un xml avec la fonction `Request::renderXml($array,$unset,$rename)`.

Cette fonction prend 3 paramètres dont 2 facultatifs, tous des tableaux.

* $array => le premier paramètre est votre tableau de donnée (array/object);
* $unset => (facultatif) liste des clés de votre tableau que vous voulez supprimer à la génération du XML. array('id','nom');
* $rename => (facultatif) renomer les balises de votre xml. array('id_table'=>'id'); modifiera <id_table>1</id_table> en < id>1< /id>

## REST

Kinto'Un permet l'utilisation de REST.
Dans le controller, testez la requète pour savoir de quel type elle est.

* POST => Request::POST();
* GET => Request::GET();
* PUT => Request::PUT();
* DELETE => Request::DELETE();

Toutes les requètes doivent être testée avec une condition et return true si la requète utilisée est celle de la condition. Une condition n'est pas obligatoire uniquement pour GET et peut envoyer directement un tableau comme option.
Pour retourner un JSON utiliser Request::renderJson($data);

Pour récupérer la valeur envoyer par une requète:
* POST => $_POST
* PUT => $this->_PUT ou Request::$_PUT
* DELETE => le paramètre de l'action
* GET => une requète de la class Model ($this->table->findAll(); / $this->table->findById($id))



```php
# lien: monsite.com/article/

public function indexAction(){
	$data = $this->table->findAll();

	Request::GET($data); # une condition n'est pas obligatoire pour GET

	if(Request::POST()){
		$this->livreor->save($_POST);
	}else{ #sinon retourne une page html
		$this->render(array(
			"articles"	=>	$data,
		));
	}
}
```

```php
# lien: monsite.com/article/44

public function viewAction($id){
	$data = $this->table->findById($id);

	Request::GET($data); # une condition n'est pas obligatoire pour GET

	if(Request::PUT()){
		$this->table->save(Request::$_PUT);
	}elseif (Request::DELETE()){
		$this->table->delete($id);
	}else{ # Sinon retourne une page html
		$this->render(array(
			"article"	=>	$data,
		));				
	}
}
```