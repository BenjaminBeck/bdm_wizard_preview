## TYPO3 Extension: Wizard-Preview


### Initial Version
![wizzard-before.png](Documentation%2Fwizzard-before.png)


### Enhanced Version
![wizzard-after.png](Documentation%2Fwizzard-after.png)

### Compatibility
ATM its only testet with TYPO3 11.5

### Installation

1. Start by adding our repository to your composer.json:
   ```yaml
   "repositories": [
     {
       "url": "https://github.com/BenjaminBeck/bdm_wizard_preview.git",
       "type": "git"
     }
   ],
   ```
2. Install the extension using composer:
   ```sh
   composer req bdm/bdm_wizard_preview:^11.5
   ```

3. Adjust the extension configuration to align with your folder structure.
   ![extension-settings.png](Documentation%2Fextension-settings.png)

4. Adding Preview Images<br>
   <br>
   Place the preview images in the specified folder. Ensure filenames correspond with the contents CType. In TYPO3 development context, if images are absent, the wizard will guide you by displaying the required filename. Additional variant images can be displayed by adding files ending with "-variant-" followed by a number. For example: If the de default preview image is "textmedia.png" the first variant image is "textmedia-variant-1.png".<br>
    <br>
   ![extension-filename-help.png](Documentation%2Fextension-filename-help.png)



