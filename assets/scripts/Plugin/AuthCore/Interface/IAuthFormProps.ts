import IFormField from './IFormField';

export default interface IAuthFormProps {
  title: string;
  endpoint: string;
  handler: () => Promise<boolean>;
  name: string;
  data: Record<string, IFormField>;
  csrfToken: string;
}
