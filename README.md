# ILIAS Course Wizard Plugin

## About this plugin
The Course Wizard Plugin is used in ILIAS to create templates for courses and apply the with the guidance of a wizard in a modal.

Even though the most useful-feature of this plugin is the Wizard-Modal, it is actually a repository object plugin.

## Patches for your ILIAS-Installation
This plugin contains two patches for your ILIAS-Installation. One of them is necessary, the other is optional (depending
on your ILIAS-configurations). They are both located in the `patches`-directory of this plugin and need to be applied
with git to your ILIAS-Installation. How to apply them is described in the installation instructions

### Course as possible subobject patch (mandatory)

This patch is located here: `CourseWizard\patches\xcwi_crs_as_possible_subobj.patch`

#### Description of the problem

Like Courses, Learning Modules and Media Casts, this plugin is defined as a "Repository Object". The repository object
which this plugin contributes to ILIAS is the "Course Template Container"-Object in which templates for courses and groups 
can be created.

In its core, ILIAS has a list of all available object-types and their properties (also called "object definition"). The 
class to manage them is coincidentally called `ilObjectDefinition`. The properties of a definition to an object-type gives 
answers to questions like "Can objects with this type be copied or linked?" or "Which object can be created in objects 
with this type?". Some properties like "Allow Copy" can be set by plugins themselves. Others like "Possible subobjects" 
are not modifiable by plugins at all. This is a problem, since one of the most important functionalities of "Course 
Template Container"-Objects is to contain course templates (hence their name).

#### What is changed by this patch?

This patch is inside the already mentioned class `ilObjectDefinition` and is quite short. It adds courses as possible
subobjects to "Course Template Container"-Objects. Due to this change, it is now possible to set RBAC permissions to the
roles, which should have "Create Course"-Permissions.

### Breadcrumbs fix (optional)

This patch is located here: `CourseWizard\patches\xcwi_breadcrumbs_fix.patch.patch`

The breadcrumbs are the part on the ILIAS-Page, which describes the path to the current object you are in. It is located 
below the meta bar (the bar at the top with the ILIAS-Logo).

#### Description of the problem

As explained for the patch "Course as possible subobject", plugin-objects are not meant to have subobjects. For the 
majority of ILIAS, the patch does not pose a problem. The only exception I found was in the breadcrumbs. When being inside
a course template, the "Course Template Container"-Object is not shown in the breadcrumbs at all.

An addition for this patch is the overwritten method `addLocatorItems()` inside of `ilObjCourseWizardGUI`. This method
makes sure, that the "Course Template Container"-Object is not added two times to the breadcrumbs when this patch is active.
You can delete `addLocatorItems()` from `ilObjCourseWizardGUI` if you don't use this patch. A config to not use this
method might be added as config later.

#### What is changed by this patch?

This patch adds "Course Template Container"-Objects to the list of "Container"-Objects in `ilLocatorGUI` (the class used 
to build breadcrumbs). The plugin does not need this patch to work. But for the user experience, it can be as small deatail

## Getting Started

### Prerequisites

### Installation Instructions
**1. Add the plugin to your ILIAS installation**

- Navigate to the root directory of your ILIAS installation on the command line
- Execute following command to create the directory for the plugin slot "Repository Object":
```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject/
```
- Switch to the directory for the plugin slot
```bash
cd Customizing/global/plugins/Services/Repository/RepositoryObject/
```
- Clone the git repository from Github
```bash
git clone https://github.com/HochschuleLuzern/CourseWizard.git
```

**2. Apply the patches**
This plugin contains two patches for your ILIAS-Repository. One is mandatory by the plugin, the other is optional.

- Navigate to the root directory of your ILIAS installation on the command line
- Enter following commands to apply the patches
```bash
# This patch is mandatory
git apply Customizing/global/plugins/Services/Repository/RepositoryObject/CourseWizard/patches/xcwi_crs_as_possible_subobj.patch

# This patch is optional
git apply Customizing/global/plugins/Services/Repository/RepositoryObject/CourseWizard/patches/xcwi_breadcrumbs_fix.patch
```
- Depending on your management of the ILIAS-Code-Base, you have to commit this patches to your repo. Otherwise, they could 
be overwritten with the next update.

**3. Install the plugin**
- Login in on ILIAS with administrator privileges
- Navigate to Administration -> Extending ILIAS -> Plugins
- Select the Actions-Dropdown and click on *"Install"*

**4. Configure the Plugin**
- In the plugin overview, select the option *"Configure"* from the actions-dropdown
- There is 1 necessary plugin-config which has to be set before the plugin is usable:

**5. Activate the plugin**
- In the plugin overview, select the option *"Activate"*

## Features

### Wizard Modal
The wizard modal is the most helpful feature in this plugin and even the reason, why it was written.

- The wizard modal is shown inside...
  - ... courses which are empty or have only groups as subobjects
  - ... groups which are inside a course
- To see the wizard, you have to be on the "Content"-Page of the target course or group object
  - Note: The wizard is not shown on the "Settings"-Page
- The wizard is divided into 4 pages:
  - Instructions: This page is display at first when the wizard modal pops up and explains how the wizard works to the user
  - Template Selection: On this page, the template for the target course/group is selected. 
    - The templates can either stem from different sources like:
      - A "Course Template Container"-Object in the scope of which the current course lies
      - A "Course Template Container"-Object defined with the scope "Global"
      - Courses / Groups, in which the executing user is defined as "Course Admin" or "Group Admin"
    - Each source will have a button to show its templates
      - The title for the button will be the title of the "Course Template Container"-Object
      - Except for the last one, which is named "My courses and groups"
    - You can switch between the sources with the respective button
  - Content Inheritance: On this page, you can select which objects of the template should be copied
    - This is indeed the same kind of table, which you will get for the "Adopt Contet"-Feature which is in the ILIAS-Core
  - Settings: On this page is a list of settings, which the user wants to have different from the template
    - All these settings can be changed in the settings import after the execution of the wizard. This is just for convenience
  - On the last page, you can execute the adoption of the content objects and content page
- Postponing the wizard
  - There are multiple ways to click the wizard away to execute it later
    - Click somewhere near the modal
    - Click on the cross in the top right corner of the modal
    - Click on the "Close Modal"-Button in the bottom right corner of the modal
    - Press the ESC-Key on your keyboard
  - After postponing the wizard, the page will reload and a blue Info-Message will be shown with a button to show the wizard again
- Dismiss the wizard
  - If the course or group should stay empty for a while and the wizard is not needed, there is also the possibility to dismiss it
    - To Dismiss the wizard, click on the "Arrange Course Unassisted" on the bottom of the wizard modal
    - Click on "Quit Wizard" to confirm the action
  - After the dismission, it will not be possible to execute the wizard again for this object
  - A possible alternative to dismiss the wizard is to just create an object in the course / group

### Course Templates
Course templates are an essential part for a lot of institutions. They can be used as a guideline for course- and group-
admins on how to arrange their courses / groups. In the context of this plugin, an already arranged course can also be seen 
as a sort of template for a new course.

## Contact
Hochschule Luzern

