#ifndef PMV_h
#define PMV_h
#include "Arduino.h"

class PMV {
    public:
    PMV();
    double calculatePMV(double tdb, double tr, double vr, double rh, double met, double clo, double wme);
	double calculatePPD(double pvm);
};

#endif