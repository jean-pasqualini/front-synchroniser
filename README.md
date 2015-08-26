#Front synchroniser

### Specification technique

On disposera d'un select **XPATH** associé à une version spécifique des **html static**

Egalement on disposera de **Point de pivot** permettant de passer d'un version des **html static** à une autre 

#### Le verrou 

Le verrou associe un contenu back à une version **htlm static**

##### XPATH

XPATH est un selecteur pour injecter un contenu **FIABLE** mais non **FLEXIBLE**

La flexibilité une fois que le contenu ne change pas on n'en à plus utiliter

Il sera ciblé exactement les partie ou le contenu back doit être injecter

#### Point de pivot

Le point de pivot se doit d'être **FLEXIBLE** avant d'être **FIABLE**

Un point de pivot permet de cibler la nouvelle partie de la version T + 1 des **html static**.

S'il est suffisament fiable, il peut le faire sans confirmation du développeur.
S'il n'est pas suffisament fiable, il demandera au développeur de l'aiguiller dans son choix.

Il y aura plusieur implémentation possible du point de pivot 

##### Le selecteur css flexique

Le point de pivot se base sur des sélecteur css de niveau de fiabilité différent.

C'est donc un point de pivot hybrique qui dans la plupart des cas agira automatiquement mais pourrais demander l'aiguillage du développeur dans les cas plus hardu.

1. TYPE + CLASS + ID + DESCENDANCE [FIABLE]
2. TYPE + CLASS + __ + DESCENDANCE [FIABLE]
3. TYPE + _____ + __ + DESCENDANCE [FIABLE SI UNIQUE]
4. TYPE + _____ + __ + ___________ [FIABLE SI UNIQUE]

Il sera possible de faire un analyse dom du **html static** afin de générer de facon intélégente de nouveau selecteur **fiable** et **flexible**

##### Le merge avec le git front

- . . . . Branch front T . . . . . . . . . Branche front T + 1
- . . . . . Branche front T + BACK . . . . . Branche front T + 1 + Back



