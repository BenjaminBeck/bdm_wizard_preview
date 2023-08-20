
## TYPO3 Extension: Wizard-Preview


### Before
![wizzard-before.png](Documentation%2Fwizzard-before.png)


### After
![wizzard-after.png](Documentation%2Fwizzard-after.png)




### Compatibility
ATM its only testet with TYPO3 11.5

### Installation

1. Add repository to composer.json
   ```yaml
   "repositories": [
     {
       "url": "https://github.com/BenjaminBeck/bdm_wizard_preview.git",
       "type": "git"
     }
   ],
   ```
2. Install via composer
   ```sh
   composer req bdm/bdm_wizard_preview:^11.5
   ```

3. Set extension configuration to match your folder structure
   ![extension-settings.png](Documentation%2Fextension-settings.png)

4. Place the preview images in the configured folder. The filenames must match the identifier of the content-icon inside the new content wizard. As a help the required filename is shown in the wizard if TYPO3 is in development context and no image has been found.
   ![extension-filename-help.png](Documentation%2Fextension-filename-help.png)



