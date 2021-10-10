class JWT<TClaim>{

  private iss: string = null;
  get Issuer(): string { return this.iss; }

  private sub: string = null;
  get Subject(): string { return this.sub; }

  private aud: string = null;
  get Audiance(): string { return this.aud; }

  private exp: Date = new Date(0);
  get Expiring(): Date { return this.exp; }

  private nbf: Date = new Date(0);
  get NotBefore(): Date { return this.nbf; }

  private iat: Date = new Date(0);
  get IssuedAt(): Date { return this.iat; }

  private jti: string = null;
  get JwtId(): string { return this.jti; }

  private claims: TClaim;
  get Claims(): TClaim { return this.claims; }

  constructor(token: string) {
    let body = JSON.parse(atob(token.split('.')[1]));
    this.claims = <TClaim>{};
    let anyClaim = <any>this.claims;
    let anyThis = <any>this;
    for (var par in body) {
      if (anyThis[par] !== undefined) {
        if (anyClaim[par] instanceof Date)
          anyThis[par] = new Date(body[par] * 1000)
        else
          anyThis[par] = body[par];
      } else {
        anyClaim[par] = body[par];
      }
    }
  }
}

enum Privilege {
  User = 1,
  Moderator,
  Admin
}

class UserClaim {
  username: string;
  privilege: Privilege;
}