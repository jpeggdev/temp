export interface SendTestEmailRequest {
  emailRecipients: string[];
  emailSubject: string | null | undefined;
  emailTemplateId: number;
  eventId: number;
  sessionId: number;
}

export interface SendTestEmailResponse {
  data: {
    message: string;
  };
}
