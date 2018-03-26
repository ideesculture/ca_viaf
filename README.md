# VIAF Information Service for CollectiveAccess

[InformationService](http://docs.collectiveaccess.org/wiki/Information_Services) for [CollectiveAccess](https://github.com/collectiveaccess/providence). Queries the Virtual Internet Authority Files (VIAF) for links to VIAF reference.

## Template of this repository

Structure, folders and documentation here mimics the IconClass Information Service by Karl Becker : https://github.com/karbecker/ca_iconclass

## Installation

- Copy the Viaf.php to `your_providence_install/app/lib/core/Plugins/InformationService/Tematres.php`
- Create a Metadata Element with Viaf as Information Service, let's call it "VIAF Reference", having viaf_reference as its idno.

## Pawtucket2

Call it in [Pawtucket2](https://github.com/collectiveaccess/pawtucket2), the CA Frontend (for instance in a ca_objects_default_html.php if the Metadata Element is called viaf_reference (depending on what you chose before) : 

    {{{<a href="^ca_objects.viaf_reference.url">^ca_objects.viaf_reference</a>}}}
