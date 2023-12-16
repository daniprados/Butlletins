# Eina per extreure els butlletins del SAGA

El SAGA és una eina que patim els docents de cicles, i com tots sabem està pensada per facilitar la vida al professorat 😉.

A partir d'un PDF de butlletins d'un grup del SAGA extreu un PDF per cada alumne amb el seu nom en format "cognoms, noms.pdf".

Els fitxers de butlletins han de tenir el mateix nom que el grup en el SAGA. Si tenim una grup de 1r de DAW que al SAGA està entrat com DAW1A el fitxer de butlletins s'ha d'anomenar DAW1A.pdf i extreura els butlletins a la carpeta 'Butlletins/DAW1A/'.

## Funcionament de l'aplicació
Un cop tenim el fitxer PDF dels butlletins del grup que volem extreure a la mateixa carpeta que l'eina.

```sh
$ php butlletins.php -h
# Ens escriurà la informació d'ajuda.
$ php butlletins.php -c 
# Copia tots els bulletins que troba a la carpeta del grup que les pertoca, cal que tinguin el mateix nom que el grup de SAGA
$ php butlletins.php DAW1A
# Extraurà els butlletins del fitxer DAW1A.pdf a la carpeta Butlletins/DAW1A/
```


## Requisits de l'eina per funcionar
PHP 8 o superior.  Ha estat provat amb PHP 8.2, però no ha de tenir problemes amb altres versions, utilitza funcionalitats molt bàsiques.

Per fer l'extracció s'utilitza les eines pdftk i pdftotxt.

L'eina pdftk permet extreu pàgines d'un pdf o comptar quantes pàgines té.
L'eina pdftotxt ens permet detectar el nom de l'alumne en una pàgina concreta.

Per funcionar necessita un CSV exportat del SAGA amb els camps

```csv
#,00_IDENTIFICADOR DE L'ALUMNE/A,01_NOM,02_DATA NAIXEMENT,03_RESPONSABLE 1,04_TELÈFON RESP. 1,05_MÒBIL RESP. 1,06_ADREÇA ELECTR. RESP. 1,07_RESPONSABLE 2,08_TELÈFON RESP. 2,09_MÒBIL RESP. 2,10_ADREÇA ELECTR. RESP. 2,11_ADREÇA,12_LOCALITAT,13_MUNICIPI,14_CORREU ELECTRÒNIC,15_ALTRES TELÈFONS,16_CENTRE PROCEDÈNCIA,17_GRUPSCLASSE,18_GRUPS,19_NIVELL
```

L'utilitza per poder desar els fitxers amb el format "cognoms, nom.pdf". Així els PDF es poden ordenar alfabèticament.


## Llicència

Aquest projecte té la llicència MIT.

Dani Prados (2023)