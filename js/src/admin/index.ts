import app from 'flarum/admin/app';
import ProfanityCheckerSettingsModal from './components/ProfanityCheckerSettingsModal';

app.initializers.add('bhzoon-profanity-checker', () => {
  app.extensionData
    .for('bhzoon-profanity-checker')
    .registerPage(ProfanityCheckerSettingsModal)
    .registerPermission(
        {
          icon: 'fas fa-shield-alt',
          label: app.translator.trans('bhzoon-profanity-checker.admin.permissions.bypass_label'),
          permission: 'bhzoon.profanity.bypass',
        },
        'moderate'
      );
});
