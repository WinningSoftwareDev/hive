export interface IUser {
  id?: number;
  email: string;
  roles?: string[];
  createdAt?: string;
  verified: boolean;
}
