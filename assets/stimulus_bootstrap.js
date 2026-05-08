import { startStimulusApp } from '@symfony/stimulus-bundle';
import SubmissionModalController from './controllers/submission_modal_controller';

const app = startStimulusApp();
app.register('submission-modal', SubmissionModalController);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
